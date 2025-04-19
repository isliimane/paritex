<?php

namespace App\Http\Controllers\Admin\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductStock;
use Toastr;

class WarehouseTransferController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = WarehouseTransfer::with([
            'fromWarehouse.warehouseLanguages',
            'toWarehouse.warehouseLanguages',
            // 'product.productLanguages',
            // 'productStock',
            'createdBy'
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('fromWarehouse.warehouseLanguages', function ($subQuery) use ($search) {
                      $subQuery->where('code', 'like', "%{$search}%")
                      ->OrWhere('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('toWarehouse.warehouseLanguages', function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                    ->OrWhere('name', 'like', "%{$search}%");
                  });
            });
        }

        $transfers = $query->latest()->paginate(10)->appends(['search' => $search]);

        return view('admin.warehouses.transfers.index', compact('transfers', 'search'));
    }

    public function create()
    {
        $warehouses = Warehouse::active()->get();
        return view('admin.warehouses.transfers.create', compact('warehouses'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'from_warehouse_id' => 'required|exists:warehouses,id',
                'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.product_stock_id' => 'required|exists:product_stocks,id',
                'items.*.quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string',
            ]);

            // Check destination warehouse capacity
            $destinationWarehouse = Warehouse::findOrFail($request->to_warehouse_id);
            $totalQuantityInDestination = WarehouseProduct::where('warehouse_id', $request->to_warehouse_id)
                ->sum('quantity');

            $totalTransferQuantity = array_sum(array_column($request->items, 'quantity'));
            if (($totalQuantityInDestination + $totalTransferQuantity) > $destinationWarehouse->storage_capacity) {
                $availableSpace = $destinationWarehouse->storage_capacity - $totalQuantityInDestination;
                throw new \Exception(__('Destination warehouse capacity exceeded. Available space: :space', ['space' => $availableSpace]));
            }

            // Check source warehouse stock for each item
            foreach ($request->items as $item) {
                $sourceProduct = WarehouseProduct::where('warehouse_id', $request->from_warehouse_id)
                    ->where('product_id', $item['product_id'])
                    ->where('product_stock_id', $item['product_stock_id'])
                    ->first();

                if (!$sourceProduct || $sourceProduct->quantity < $item['quantity']) {
                    throw new \Exception(__('Not enough stock available in source warehouse for product ID: :id', ['id' => $item['product_id']]));
                }
            }

            // Create transfer record
            $transfer = WarehouseTransfer::create([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'notes' => $request->notes,
                'created_by' => authId()
            ]);

            // Create transfer items
            foreach ($request->items as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'product_stock_id' => $item['product_stock_id'],
                    'transfer_id' => $transfer->id,
                    'quantity' => $item['quantity']
                ]);
            }

            DB::commit();

            Toastr::success(__('Transfer request created successfully'));
            return redirect()->route('transfers.index');
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            Toastr::error($e);
            return back()->withInput();
        }
    }

    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $transfer = WarehouseTransfer::with('items')->findOrFail($id);
            
            // Validate transfer status
            if ($transfer->status !== 'pending') {
                \Log::error('Transfer already processed', [
                    'transfer_id' => $id,
                    'current_status' => $transfer->status
                ]);
                throw new \Exception(__('This transfer request has already been processed'));
            }

            // Check destination warehouse capacity
            $destinationWarehouse = Warehouse::findOrFail($transfer->to_warehouse_id);
            $totalQuantityInDestination = WarehouseProduct::where('warehouse_id', $transfer->to_warehouse_id)
                ->sum('quantity');

            $totalTransferQuantity = $transfer->items->sum('quantity');
            if (($totalQuantityInDestination + $totalTransferQuantity) > $destinationWarehouse->storage_capacity) {
                $availableSpace = $destinationWarehouse->storage_capacity - $totalQuantityInDestination;
                throw new \Exception(__('Destination warehouse capacity exceeded. Available space: :space', ['space' => $availableSpace]));
            }

            // Process each item
            foreach ($transfer->items as $item) {
                // Check source warehouse stock
                $sourceProduct = WarehouseProduct::where('warehouse_id', $transfer->from_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->where('product_stock_id', $item->product_stock_id)
                    ->first();

                if (!$sourceProduct) {
                    throw new \Exception(__('Product not found in source warehouse for product ID: :id', ['id' => $item->product_id]));
                }

                if ($sourceProduct->quantity < $item->quantity) {
                    throw new \Exception(__('Not enough stock available in source warehouse for product ID: :id', ['id' => $item->product_id]));
                }

                // Update source warehouse stock
                $sourceProduct->decrement('quantity', $item->quantity);

                // Update or create destination warehouse stock
                $destinationProduct = WarehouseProduct::firstOrNew([
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'product_id' => $item->product_id,
                    'product_stock_id' => $item->product_stock_id
                ]);

                $destinationProduct->quantity = ($destinationProduct->quantity ?? 0) + $item->quantity;
                $destinationProduct->save();
            }

            // Update transfer status
            $transfer->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);

            DB::commit();

            \Log::info('Transfer completed successfully', [
                'transfer_id' => $id,
                'from_warehouse' => $transfer->from_warehouse_id,
                'to_warehouse' => $transfer->to_warehouse_id,
                'total_quantity' => $totalTransferQuantity
            ]);

            Toastr::success(__('Transfer completed successfully'));
            return redirect()->route('transfers.index');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transfer approval failed', [
                'transfer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function reject($id)
    {
        try {
            $transfer = WarehouseTransfer::findOrFail($id);
            
            if ($transfer->status !== 'pending') {
                throw new \Exception(__('This transfer request has already been processed'));
            }

            $transfer->update(['status' => 'rejected']);

            Toastr::success(__('Transfer request rejected'));
            return redirect()->route('transfers.index');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $transfer = WarehouseTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product', 'items.productStock'])
            ->findOrFail($id);

        // Check if transfer can be edited (only pending transfers can be edited)
        if ($transfer->status !== 'pending') {
            return redirect()->route('transfers.index')
                ->with('error', __('Only pending transfers can be edited.'));
        }

        $warehouses = Warehouse::where('status', 1)->get();
        
        // Get all products from the source warehouse
        $products = $transfer->fromWarehouse->products;
        
        // Get stocks for each product in the transfer
        $stocks = [];
        foreach ($transfer->items as $item) {
            $stocks[$item->product_id] = $this->getWarehouseStocks($transfer->from_warehouse_id, $item->product_id);
        }

        return view('admin.warehouses.transfers.edit', compact('transfer', 'warehouses', 'products', 'stocks'));
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
                    return (object)[
                        'id' => $warehouseProduct->productStock->id,
                        'sku' => $warehouseProduct->productStock->sku,
                        'name' => $warehouseProduct->productStock->name,
                        'quantity' => $warehouseProduct->quantity
                    ];
                });

            return $stocks;
        } catch (\Exception $e) {
            \Log::error('Error in getWarehouseStocks: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $transfer = WarehouseTransfer::findOrFail($id);
            
            // Validate the request
            $request->validate([
                'from_warehouse_id' => 'required|exists:warehouses,id',
                'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.product_stock_id' => 'required|exists:product_stocks,id',
                'items.*.quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string'
            ]);

            // Check if transfer is in pending status
            if ($transfer->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => __('Transfer cannot be updated as it is not in pending status.')
                ], 422);
            }

            // Check destination warehouse capacity
            $destinationWarehouse = Warehouse::findOrFail($request->to_warehouse_id);
            $totalQuantity = array_sum(array_column($request->items, 'quantity'));
            
            if ($destinationWarehouse->storage_capacity < $totalQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => __('Destination warehouse does not have enough capacity.')
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Update transfer details
                $transfer->update([
                    'from_warehouse_id' => $request->from_warehouse_id,
                    'to_warehouse_id' => $request->to_warehouse_id,
                    'notes' => $request->notes
                ]);

                // Delete existing items
                $transfer->items()->delete();

                // Create new items
                foreach ($request->items as $item) {
                    // Check stock availability in source warehouse
                    $sourceStock = WarehouseProduct::where('warehouse_id', $request->from_warehouse_id)
                        ->where('product_id', $item['product_id'])
                        ->where('product_stock_id', $item['product_stock_id'])
                        ->first();

                    if (!$sourceStock || $sourceStock->quantity < $item['quantity']) {
                        throw new \Exception(__('Insufficient stock in source warehouse for product: ') . $sourceStock->product->name);
                    }

                    // Create transfer item
                    $transfer->items()->create([
                        'product_id' => $item['product_id'],
                        'product_stock_id' => $item['product_stock_id'],
                        'quantity' => $item['quantity']
                    ]);
                }

                DB::commit();

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => __('Transfer updated successfully.')
                    ]);
                }
                Toastr::success(__('Transfer updated successfully'));
                return redirect()->route('transfers.index');

            } catch (\Exception $e) {
                dd($e->getMessage());
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }
} 