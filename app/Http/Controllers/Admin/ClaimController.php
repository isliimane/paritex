<?php

namespace App\Http\Controllers\Admin;
use App\Repositories\Interfaces\Admin\ClaimInterface;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Claim;

class ClaimController extends Controller
{
    protected $claimService; // Nom cohérent partout

    public function __construct(ClaimInterface $claimService) 
    {
        $this->claimService = $claimService; // Initialisation correcte
    }

    public function index()
    {
        try {
            $claims = $this->claimService->allClaims(); // Utilisez $claimService
            return view('admin.claim.index', compact('claims'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    // Corrigez aussi show() et update() en utilisant $claimService au lieu de $claim
    public function show($id)
    {
       
        try {
            // Chargez explicitement les relations nécessaires
            $claim = $this->claimService->find($id, ['user', 'order']);  
            
            if (!$claim) {
                throw new \Exception(__('Claim not found'));
            }
            return view('admin.claim.show', compact('claim'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->only(['status', 'admin_response']);
            $this->claimService->update($id, $data); // Correction ici
            Toastr::success(__('Réclamation mise à jour avec succès.'));
            return redirect()->route('admin.claim.index');  

    } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

  
    // Dans app/Http/Controllers/ClaimController.php
public function store(Request $request)
{
    
    // Log de débogage 1 - Données reçues
    Log::info('Submission received', $request->all());
    
    // Log de débogage 2 - Utilisateur authentifié
    // Log::debug('User attempting claim', [
    //     'user_id' => auth()->id(),
    //     'user_email' => auth()->user()->email
    // ]);

    $validated = $request->validate([
        'subject' => 'required|string|max:255',
        'description' => 'required|string',
        'order_id' => 'nullable|exists:orders,id'
    ]);

    try {
        $claim = Claim:: create([
            'subject' => $validated['subject'],
            'user_id' => $request['user_id'],
            'description' => $validated['description'],
            'order_id' => $validated['order_id'] ?? null,
            'status' => 'pending'
        ]);

        // Log de succès
        // Log::channel('claims')->info('Claim created successfully', [
        //     'claim_id' => $claim->id,
        //     'subject' => $claim->subject
        // ]);

        return response()->json([
            'success' => true,
            'claim' => $claim
        ], 201);

    } catch (\Exception $e) {
        // Log d'erreur
        Log::channel('claims')->error('Claim creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
}
