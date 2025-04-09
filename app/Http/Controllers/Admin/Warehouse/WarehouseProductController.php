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
            $search = request()->search;
            
            $query = $warehouse->warehouseProducts()->with(['product', 'productStock']);
            
            if ($search) {
                $query->whereHas('product', function($q) use ($search) {
                    $q->whereHas('productLanguages', function($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
                })->orWhereHas('productStock', function($q) use ($search) {
                    $q->where('sku', 'like', '%' . $search . '%');
                });
            }
            
            $products = $query->paginate(10);
            
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
            
            return view('admin.warehouses.products.index', compact('warehouse', 'products', 'allProducts', 'search'));
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

    public function getWarehouseStocks($warehouseId, $productId)
    {
        try {
            // Get stocks for the product that exist in the warehouse
            $stocks = WarehouseProduct::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->with('productStock')
                ->get()
                ->map(function($warehouseProduct) {
                    return [
                        'id' => $warehouseProduct->productStock->id,
                        'sku' => $warehouseProduct->productStock->sku,
                        'name' => $warehouseProduct->productStock->name,
                        'quantity' => $warehouseProduct->quantity
                    ];
                });

            return response()->json($stocks);
        } catch (\Exception $e) {
            \Log::error('Error in getWarehouseStocks: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getProducts(Request $request)
    {
        try {
            $warehouseId = $request->warehouse_id;
            $products = WarehouseProduct::where('warehouse_id', $warehouseId)
                ->with(['product' => function($query) {
                    $query->with(['productLanguages' => function($q) {
                        $q->where('lang', app()->getLocale());
                    }]);
                }])
                ->select('product_id')
                ->distinct()
                ->get()
                ->map(function($warehouseProduct) {
                    return [
                        'id' => $warehouseProduct->product->id,
                        'product_name' => $warehouseProduct->product->getTranslation('name', app()->getLocale())
                    ];
                });

            return response()->json($products);
        } catch (\Exception $e) {
            \Log::error('Error in getProducts: ' . $e->getMessage());
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
                $availableQuantity = $stock->current_stock - $totalStockInWarehouses;
                throw new \Exception(__('Not enough stock available. Available quantity: :quantity', ['quantity' => $availableQuantity]));
            }

            // Validate warehouse capacity
            if (($totalQuantityInWarehouse + $request->quantity) > $warehouse->storage_capacity) {
                $availableSpace = $warehouse->storage_capacity - $totalQuantityInWarehouse;
                throw new \Exception(__('Warehouse capacity exceeded. Available space: :space', ['space' => $availableSpace]));
            }

            if ($existingProduct) {
                // Update existing product
                $existingProduct->quantity += $request->quantity;
                $existingProduct->save();
            } else {
                // Create new warehouse product
                $warehouseProduct = new WarehouseProduct();
                $warehouseProduct->warehouse_id = $warehouse->id;
                $warehouseProduct->product_id = $product->id;
                $warehouseProduct->product_stock_id = $stock->id;
                $warehouseProduct->quantity = $request->quantity;
                $warehouseProduct->save();
            }

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Product added to warehouse successfully'),
                    'title' => __('Success')
                ]);
            }
            
            Toastr::success(__('Product added to warehouse successfully'), __('Success'));
            return redirect()->route('warehouse.products.index', $warehouseId);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'title' => __('Error')
                ], 500);
            }
            
            Toastr::error($e->getMessage(), __('Error'));
            return back();
        }
    }

    public function update(Request $request, $warehouseId, $id)
    {
        try {
            DB::beginTransaction();
            
            $warehouseProduct = WarehouseProduct::findOrFail($id);
            $warehouseProduct->quantity = $request->quantity;
            $warehouseProduct->save();

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Product updated successfully'),
                    'title' => __('Success')
                ]);
            }
            
            Toastr::success(__('Product updated successfully'), __('Success'));
            return redirect()->route('warehouse.products.index', $warehouseId);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'title' => __('Error')
                ], 500);
            }
            
            Toastr::error($e->getMessage(), __('Error'));
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
                    'message' => __('Product removed from warehouse successfully'),
                    'title' => __('Success')
                ]);
            }
            
            Toastr::success(__('Product removed from warehouse successfully'), __('Success'));
            return redirect()->route('warehouse.products.index', $warehouseId);
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax()) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'title' => __('Error')
                ], 500);
            }
            
            Toastr::error($e->getMessage(), __('Error'));
            return back();
        }
    }
} 