<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\Models\ReturnRequest;
use Illuminate\Support\Facades\View;

class ReturnRequestController extends Controller{

    public function store(Request $request) {
        \Log::info($request);
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:500',
        ]);
        $validated['user_id'] = authId();
        $returnRequest = ReturnRequest::create($validated);
        \Log::info($returnRequest);
        return response()->json($returnRequest, 201);
    }

    
    public function index(Request $request)
{
    $query = ReturnRequest::with(['user', 'order'])
        ->latest();
    // Filtrage par statut si présent dans l'URL
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }
    
    $returnRequests = $query->paginate(15);
    
    return view('admin.return.index', compact('returnRequests'));
}
public function update(Request $request, ReturnRequest $returnRequest) {
    $returnRequest->update(['status' => $request->status]);
    return $returnRequest;
}

// public function __construct()
// {
//     $pendingCount = ReturnRequest::where('status', 'pending')->count();
//     View::share('pendingReturnsCount', $pendingCount);
// }
}