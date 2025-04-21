<?php
// app/Http/Controllers/Frontend/ClaimController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\ClaimInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            \Log::info('calin page available');
            // $claims = $this->claim->userClaims(Auth::id());
            
            // return response()->json([
            //     'success' => true,
            //     'claims' => $claims
            // ]);
            return 'working';
        } catch (\Exception $e) {
            return 'error';
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'order_id' => 'nullable|exists:orders,id'
        ]);

        try {
            $data = $request->only(['subject', 'description', 'order_id']);
            $data['user_id'] = Auth::id();
            
            $claim = $this->claim->create($data);
            
            return response()->json([
                'success' => true,
                'message' => __('Claim submitted successfully'),
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
            
            // Vérifier que le claim appartient à l'utilisateur
            if ($claim->user_id != Auth::id()) {
                return response()->json([
                    'error' => __('Unauthorized access')
                ], 403);
            }
            
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