<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ClaimInterface;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    protected $claim;

    public function __construct(ClaimInterface $claim)
    {
        $this->claim = $claim;
    }

    public function index()
    {
        try {
            $claims = $this->claim->allClaims();
            
            return response()->json([
                'success' => true,
                'claims' => $claims
            ]);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,rejected',
            'admin_response' => 'required_if:status,resolved,rejected|string'
        ]);

        try {
            $data = $request->only(['status', 'admin_response']);
            
            if (in_array($request->status, ['resolved', 'rejected'])) {
                $data['response_date'] = now();
            }
            
            $claim = $this->claim->update($id, $data);
            
            return response()->json([
                'success' => true,
                'message' => __('Claim updated successfully'),
                'claim' => $claim
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $claim = $this->claim->find($id);
            
            return response()->json([
                'success' => true,
                'claim' => $claim
            ]);
        } catch (\Exception $e) {

            
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}