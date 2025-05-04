<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use App\Models\WarehouseTransfer;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['warehouse', 'product', 'productStock', 'user'])
            ->select(
                'stock_movements.*',
                DB::raw('COUNT(*) as total_movements'),
                // DB::raw('SUM(CASE WHEN movement_type = "in" THEN quantity ELSE -quantity END) as net_quantity')
            )
            ->groupBy('related_id', 'movement_reason')
            ->latest();

        // Apply filters if provided
        if ($request->has('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->has('movement_reason')) {
            $query->where('movement_reason', $request->movement_reason);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(20);
        $warehouses = Warehouse::all();

        // Load transfer information for transfer movements
        foreach ($movements as $movement) {
            if ($movement->movement_reason === 'transfer') {
                $transfer = WarehouseTransfer::find($movement->related_id);
                if ($transfer) {
                    $movement->transfer = $transfer;
                }
            }
        }

        return view('admin.stock-movements.index', compact('movements', 'warehouses'));
    }

    public function getDetails(Request $request)
    {
        $movements = StockMovement::with(['warehouse', 'product', 'productStock', 'user'])
            ->where('related_id', $request->related_id)
            ->where('movement_reason', $request->movement_reason)
            ->latest()->orderBy('movement_type')
            ->get();

        return view('admin.stock-movements.details', compact('movements'));
    }
} 