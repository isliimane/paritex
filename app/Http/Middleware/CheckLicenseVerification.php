<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sentinel;

class CheckLicenseVerification
{
    public function handle(Request $request, Closure $next)
    {
        if (Sentinel::check()) {
            $user = Sentinel::getUser();
            
            if ($user->user_type == 'customer' && !$user->license_verified) {
                if ($request->ajax()) {
                    return response()->json([
                        'error' => __('Your license is not verified. Please verify your license to continue.')
                    ], 403);
                }
                
                return redirect()->route('user.profile')->with('error', __('Your license is not verified. Please verify your license to continue.'));
            }
        }
        
        return $next($request);
    }
} 