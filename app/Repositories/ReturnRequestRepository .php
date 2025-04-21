<?php

namespace App\Repositories;

use App\Models\ReturnRequest;
use App\Interfaces\Admin\ReturnRequestRepositoryInterface;

class ReturnRequestRepository implements ReturnRequestRepositoryInterface
{
    public function createReturnRequest(array $data)
    {
        return ReturnRequest::create($data);
    }

    public function getReturnRequestsForUser(int $userId)
    {
        return ReturnRequest::where('user_id', $userId)
            ->with('order')
            ->get();
    }

    public function updateReturnRequestStatus(int $requestId, string $status)
    {
        $request = ReturnRequest::findOrFail($requestId);
        $request->update(['status' => $status]);
        return $request;
    }

    public function getReturnRequestById(int $requestId)
    {
        return ReturnRequest::with(['user', 'order'])
            ->findOrFail($requestId);
    }
    //pour admin
    public function processReturnRequest(int $requestId, string $resolution, string $notes = null) {
        $request = ReturnRequest::findOrFail($requestId);
        
        $updateData = [
            'status' => 'approved',
            'resolution_type' => $resolution,
            'admin_notes' => $notes,
            'processed_at' => now()
        ];
    
        $request->update($updateData);
    
        // Déclencher l'action appropriée
        if ($resolution === 'refund') {
            $this->processRefund($request);
        } else {
            $this->processExchange($request);
        }
    
        return $request;
    }
    
    private function processRefund(ReturnRequest $request) {
        // Logique de remboursement
        $order = $request->order;
        // ... implémentez votre logique de remboursement ici ...
    }
    
    private function processExchange(ReturnRequest $request) {
        // Envoi d'email pour l'échange
        $user = $request->user;
        Mail::to($user->email)->send(new ProductExchangeMail($request));
    }

    public function pendingRequests() {
        return ReturnRequest::with(['user', 'order'])
            ->pending()
            ->get();
    }
    
    public function processRequest(Request $request, $id) {
        $validated = $request->validate([
            'resolution' => 'required|in:refund,exchange',
            'notes' => 'nullable|string'
        ]);
    
        $processedRequest = $this->returnRequestRepository
            ->processReturnRequest($id, $validated['resolution'], $validated['notes']);
    
        return response()->json([
            'message' => 'Demande traitée avec succès',
            'data' => $processedRequest
        ]);
    }
//analyse automatique 
// public function analyzeReturnReason(ReturnRequest $request) {
//     $keywords = [
//         'cassé' => 'exchange',
//         'défectueux' => 'exchange',
//         'mauvais' => 'exchange',
//         'erreur' => 'exchange',
//         'remboursement' => 'refund',
//         'argent' => 'refund'
//     ];

//     foreach ($keywords as $keyword => $resolution) {
//         if (stripos($request->reason, $keyword) !== false) {
//             return $resolution;
//         }
//     }

//     return $request->is_product_unused ? 'refund' : 'exchange';
// }

}