<?php

namespace App\Http\Controllers\Admin\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Toastr;

class WarehouseTransferController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = WarehouseTransfer::with([
            'fromWarehouse.warehouseLanguages',
            'toWarehouse.warehouseLanguages',
            'product.productLanguages',
            'productStock',
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
                  })
                  ->orWhereHas('product.productLanguages', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', "%{$search}%");
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
                'product_id' => 'required|exists:products,id',
                'product_stock_id' => 'required|exists:product_stocks,id',
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string',
            ]);
            // Check if product exists in source warehouse
            $sourceProduct = WarehouseProduct::where('warehouse_id', $request->from_warehouse_id)
                ->where('product_id', $request->product_id)
                ->where('product_stock_id', $request->product_stock_id)
                ->first();

            if (!$sourceProduct || $sourceProduct->quantity < $request->quantity) {
                throw new \Exception(__('Not enough stock available in source warehouse'));
            }

            // Check destination warehouse capacity
            $destinationWarehouse = Warehouse::findOrFail($request->to_warehouse_id);
            $totalQuantityInDestination = WarehouseProduct::where('warehouse_id', $request->to_warehouse_id)
                ->sum('quantity');

            if (($totalQuantityInDestination + $request->quantity) > $destinationWarehouse->storage_capacity) {
                $availableSpace = $destinationWarehouse->storage_capacity - $totalQuantityInDestination;
                throw new \Exception(__('Destination warehouse capacity exceeded. Available space: :space', ['space' => $availableSpace]));
            }

            // Create transfer record
            $transfer = WarehouseTransfer::create([
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'product_id' => $request->product_id,
                'product_stock_id' => $request->product_stock_id,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
                'created_by' => authId()
            ]);

            DB::commit();

            Toastr::success(__('Transfer request created successfully'));
            return redirect()->route('transfers.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return back()->withInput();
        }
    }

    public function approve($id)
    {
        try {
            DB::beginTransaction();

            $transfer = WarehouseTransfer::findOrFail($id);
            
            // Validate transfer status
            if ($transfer->status !== 'pending') {
                \Log::error('Transfer already processed', [
                    'transfer_id' => $id,
                    'current_status' => $transfer->status
                ]);
                throw new \Exception(__('This transfer request has already been processed'));
            }

            // Check source warehouse stock
            $sourceProduct = WarehouseProduct::where('warehouse_id', $transfer->from_warehouse_id)
                ->where('product_id', $transfer->product_id)
                ->where('product_stock_id', $transfer->product_stock_id)
                ->first();

            if (!$sourceProduct) {
                \Log::error('Source product not found', [
                    'transfer_id' => $id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'product_id' => $transfer->product_id,
                    'stock_id' => $transfer->product_stock_id
                ]);
                throw new \Exception(__('Product not found in source warehouse'));
            }

            if ($sourceProduct->quantity < $transfer->quantity) {
                \Log::error('Insufficient stock in source warehouse', [
                    'transfer_id' => $id,
                    'available_quantity' => $sourceProduct->quantity,
                    'requested_quantity' => $transfer->quantity
                ]);
                throw new \Exception(__('Not enough stock available in source warehouse'));
            }

            // Check destination warehouse capacity
            $destinationWarehouse = Warehouse::findOrFail($transfer->to_warehouse_id);
            $totalQuantityInDestination = WarehouseProduct::where('warehouse_id', $transfer->to_warehouse_id)
                ->sum('quantity');

            if (($totalQuantityInDestination + $transfer->quantity) > $destinationWarehouse->storage_capacity) {
                $availableSpace = $destinationWarehouse->storage_capacity - $totalQuantityInDestination;
                \Log::error('Destination warehouse capacity exceeded', [
                    'transfer_id' => $id,
                    'requested_quantity' => $transfer->quantity,
                    'available_space' => $availableSpace
                ]);
                throw new \Exception(__('Destination warehouse capacity exceeded. Available space: :space', ['space' => $availableSpace]));
            }

            // Update source warehouse stock
            $sourceProduct->decrement('quantity', $transfer->quantity);

            // Update or create destination warehouse stock
            $destinationProduct = WarehouseProduct::firstOrNew([
                'warehouse_id' => $transfer->to_warehouse_id,
                'product_id' => $transfer->product_id,
                'product_stock_id' => $transfer->product_stock_id
            ]);

            $destinationProduct->quantity = ($destinationProduct->quantity ?? 0) + $transfer->quantity;
            $destinationProduct->save();

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
                'quantity' => $transfer->quantity
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
} 