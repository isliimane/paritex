<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Site\PaymentController;
use App\Repositories\Admin\CurrencyRepository;
use App\Repositories\Interfaces\Admin\Addon\WalletInterface;
use App\Repositories\Interfaces\Admin\OrderInterface;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    protected $wallet;

    public function __construct(\App\Repositories\Interfaces\Admin\Addon\WalletInterface $wallet)
    {
        $this->wallet = $wallet;
    }

    public function walletRechargeRequests(Request $request)
    {
        try {
            $wallet_recharge_requests = $this->wallet->paginate(get_pagination('pagination'), 'wallet_recharge', $request);
            return view('admin.wallet.wallet-recharge-requests', compact('wallet_recharge_requests'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function walletApproveRequest($id)
    {
        try {
            $this->wallet->walletApproveRequest($id);
            $response['message'] = __('Approved Successfully');
            $response['status']  = 'success';
            $response['title']   = __('Success');
            return response()->json($response);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
    public function walletRejectRequest($id){

        try {
            $this->wallet->walletRejectRequest($id);
            $response['message'] = __('Reject Successfully');
            $response['status']  = 'success';
            $response['title']   = __('Success');
            return response()->json($response);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }


    public function walletStore(Request $request, \App\Repositories\Interfaces\Admin\Addon\WalletInterface $wallet, OrderInterface $order, $other = null)
    {
        $paymentController = new paymentController($order);
        $user_id =  authId();
        if ($request->payment_type == 'google_pay') {
            $payment_details = [
                'name' => '',
                'postal_code' => '',
            ];
            $data['payment_type']      = 'google_pay';
        }
        $paymentController->storeWallet($request, $user_id, $wallet, $payment_details, $data);
    }


    protected function getCurrency()
    {
        $currency = new CurrencyRepository();
        return $currency->currencyByCode('BDT');
    }
}
