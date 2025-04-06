<?php

namespace App\Http\Controllers\Admin\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Repositories\Admin\Warehouse\WarehouseProductRepository;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseProductController extends Controller
{
    protected $warehouseProductRepository;

    public function __construct(WarehouseProductRepository $warehouseProductRepository)
    {
        $this->warehouseProductRepository = $warehouseProductRepository;
       // $this->middleware(['auth', 'adminCheck']);
    }

    public function index($warehouseId)
    {
        try {
            $warehouse = Warehouse::findOrFail($warehouseId);
            $products = $warehouse->warehouseProducts()->with(['product', 'productStock'])->paginate(10);
            
            // Get all products with their stocks and calculate available quantities
            $allProducts = Product::with(['stock' => function($query) {
                $query->select('id', 'product_id', 'current_stock');
            }])->get()->map(function($product) {
                // Calculate total quantity of each stock in all warehouses
                $stockQuantities = WarehouseProduct::whereIn('product_stock_id', $product->stock->pluck('id'))
                    ->select('product_stock_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('product_stock_id')
                    ->pluck('total_quantity', 'product_stock_id');

                // Calculate available quantity for each stock
                $product->stock->each(function($stock) use ($stockQuantities) {
                    $stock->available_quantity = $stock->current_stock - ($stockQuantities[$stock->id] ?? 0);
                });

                return $product;
            });
            
            return view('admin.warehouses.products.index', compact('warehouse', 'products', 'allProducts'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function getStocks($warehouseId, $productId)
    {
        try {
            // Get all stocks for the product
            $stocks = ProductStock::where('product_id', $productId)->get();
            
            // Calculate total quantity of each stock in all warehouses
            $stockQuantities = WarehouseProduct::whereIn('product_stock_id', $stocks->pluck('id'))
                ->select('product_stock_id', DB::raw('SUM(quantity) as total_quantity'))
                ->groupBy('product_stock_id')
                ->pluck('total_quantity', 'product_stock_id');

            // Calculate available quantity for each stock
            $stocks->each(function($stock) use ($stockQuantities) {
                $stock->available_quantity = $stock->current_stock - ($stockQuantities[$stock->id] ?? 0);
            });

            return response()->json($stocks);
        } catch (\Exception $e) {
            \Log::error('Error in getStocks: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request, $warehouseId)
    {
        try {
            DB::beginTransaction();
            
            $warehouse = Warehouse::findOrFail($warehouseId);
            $product = Product::findOrFail($request->product_id);
            $stock = ProductStock::findOrFail($request->product_stock_id);

            // Check if product already exists in warehouse
            $existingProduct = WarehouseProduct::where('warehouse_id', $warehouse->id)
                ->where('product_id', $product->id)
                ->where('product_stock_id', $stock->id)
                ->first();

            // Calculate total quantity in warehouse
            $totalQuantityInWarehouse = WarehouseProduct::where('warehouse_id', $warehouse->id)
                ->sum('quantity');

            // Calculate total quantity of this stock in all warehouses
            $totalStockInWarehouses = WarehouseProduct::where('product_stock_id', $stock->id)
                ->sum('quantity');

            // Validate stock availability
            if (($totalStockInWarehouses + $request->quantity) > $stock->current_stock) {
                throw new \Exception(__('Not enough stock available. Available quantity: ') . ($stock->current_stock - $totalStockInWarehouses));
            }

            // Validate warehouse capacity
            if (($totalQuantityInWarehouse + $request->quantity) > $warehouse->storage_capacity) {
                throw new \Exception(__('Warehouse capacity exceeded. Available space: ') . ($warehouse->storage_capacity - $totalQuantityInWarehouse));
            }

            // Validate shelf and column numbers
            if ($request->shelf_number > $warehouse->number_of_shelves) {
                throw new \Exception(__('Invalid shelf number. Maximum shelves: ') . $warehouse->number_of_shelves);
            }

            if ($request->column_number > $warehouse->columns_per_shelf) {
                throw new \Exception(__('Invalid column number. Maximum columns per shelf: ') . $warehouse->columns_per_shelf);
            }

            if ($existingProduct) {
                // Update existing product
                $existingProduct->quantity += $request->quantity;
                $existingProduct->shelf_number = $request->shelf_number;
                $existingProduct->column_number = $request->column_number;
                $existingProduct->save();
            } else {
                // Create new warehouse product
                $warehouseProduct = new WarehouseProduct();
                $warehouseProduct->warehouse_id = $warehouse->id;
                $warehouseProduct->product_id = $product->id;
                $warehouseProduct->product_stock_id = $stock->id;
                $warehouseProduct->quantity = $request->quantity;
                $warehouseProduct->shelf_number = $request->shelf_number;
                $warehouseProduct->column_number = $request->column_number;
                $warehouseProduct->save();
            }

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Product added to warehouse successfully')
                ]);
            }
            
            Toastr::success(__('Product added to warehouse successfully'));
            return redirect()->route('warehouse.products.index', $warehouseId);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function update(Request $request, $warehouseId, $id)
    {
        try {
            DB::beginTransaction();
            
            $warehouseProduct = WarehouseProduct::findOrFail($id);
            $warehouseProduct->quantity = $request->quantity;
            $warehouseProduct->shelf_number = $request->shelf_number;
            $warehouseProduct->column_number = $request->column_number;
            $warehouseProduct->save();

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Product updated successfully')
                ]);
            }
            
            Toastr::success(__('Product updated successfully'));
            return redirect()->route('warehouse.products.index', $warehouseId);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function destroy($warehouseId, $id)
    {

        try {
            DB::beginTransaction();
            
            $warehouseProduct = WarehouseProduct::findOrFail($id);
            $warehouseProduct->delete();

            DB::commit();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Product removed from warehouse successfully')
                ]);
            }
            
            Toastr::success(__('Product removed from warehouse successfully'));
            return redirect()->route('warehouse.products.index', $warehouseId);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            
            Toastr::error($e->getMessage());
            return back();
        }
    }
} 