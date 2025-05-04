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

    public function mercadoPago(Request $request, OrderInterface $order)
    {
        $paymentController = new paymentController($order);

        $access_key = settingHelper('mercadopago_access_key');

        if (!$access_key) {
            return back()->with(['warning' => __('provide_correct_credential')]);
        }

        $data                       = $request->all();
        $request['payment_mode']    = 'wallet_recharge';
        $request['payment_type']    = 'mercadopago';
        //        $data                       = $paymentController->tokenGenerator($data);
        $success_url                = $paymentController->successUrl($request);

        $billing_details = [
            'name' => '',
            'postal_code' => '',
        ];

        $data = [
            'success_url'       => $success_url,
            'fail_url'          => url()->previous(),
            'billing_details'   => $billing_details,
            'amount'            => $request->amount
        ];

        return view('frontend.payments.mercado_pago', $data);
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
        }else if ($request->payment_type == 'mercadopago') {
            $payment_details = [
                'name' => '',
                'postal_code' => '',
            ];
            $data['payment_type']      = 'mercadopago';
        }
        $paymentController->storeWallet($request, $user_id, $wallet, $payment_details, $data);
    }


    public function skrillRedirect(Request $request, OrderInterface $order, WalletInterface $wallet): \Illuminate\Http\RedirectResponse
    {
        $paymentController = new paymentController($order);
        $data                               = $request->all();
        $request['payment_type']            = 'skrill';
        $active_currency                    = $paymentController->activeCurrencyCheck($data);
        $success_url                        = $paymentController->successUrl($request);
        $logo                               = settingHelper('dark_logo') != [] && @is_file_exists(settingHelper('dark_logo')['original_image']) ?  get_media(@settingHelper('dark_logo')['original_image'], @settingHelper('dark_logo')['storage']) : static_asset('images/default/dark-logo.png');

        $skrilRequest                       = new SkrillRequest();
        $skrilRequest->pay_to_email         = settingHelper('skrill_merchant_email'); // your merchant email
        $skrilRequest->return_url           = $success_url;
        $skrilRequest->cancel_url           = url()->previous();
        $skrilRequest->logo_url             = $logo;  // optional
        $skrilRequest->status_url           = $success_url;
        $skrilRequest->amount               = round($request->amount * $active_currency->exchange_rate, 2);
        $skrilRequest->currency             = $active_currency->code;
        $skrilRequest->language             = 'EN';
        $skrilRequest->country              = $paymentController->findSystemCountry();
        $skrilRequest->prepare_only         = '1';
        $skrilRequest->merchant_fields      = 'site_name, customer_email';
        $skrilRequest->site_name            = settingHelper('system_name');
        $skrilRequest->customer_email       = authUser()->email;

        $client     = new SkrillClient($skrilRequest);
        $sid        = $client->generateSID(); //return SESSION ID
        $jsonSID    = json_decode($sid);

        if ($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
            return $jsonSID->message;

        $data_info = [
            'amount' => $request->amount,
            'payment_type' => 'skrill',
        ];

        $this->walletStore($request, $wallet, $order, $data_info);

        //        $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url
        //        return redirect()->to($redirectUrl);
    }

    public function iyzicoRedirect(Request $request, OrderInterface $order, \App\Repositories\Interfaces\Admin\Addon\WalletInterface $wallet)
    {
        $paymentController = new paymentController($order);
        try {
            if (settingHelper('is_iyzico_sandbox_enabled')) {
                $url = "https://sandbox-api.iyzipay.com";
            } else {
                $url = "https://api.iyzipay.com";
            }
            $data = $request->all();
            $user = $paymentController->findUser($data);
            $amount = round($request->amount, 2);
            $basket_id = date('YmdHis');
            $conversation_id = rand(100000000, 999999999);

            $options = new \Iyzipay\Options();
            $options->setApiKey(settingHelper('iyzico_api_key'));
            $options->setSecretKey(settingHelper('iyzico_secret_key'));
            $options->setBaseUrl($url);
            $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
            if (settingHelper('default_language') == 'tr') {
                $request->setLocale(\Iyzipay\Model\Locale::TR);
            } else {
                $request->setLocale(\Iyzipay\Model\Locale::EN);
            }
            $request->setConversationId($conversation_id);
            $request->setPrice($amount);
            $request->setPaidPrice($amount);
            $request->setCurrency(\Iyzipay\Model\Currency::TL);
            $request->setLocale(\Iyzipay\Model\Locale::EN);
            $request->setBasketId($basket_id);
            $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
            $request->setCallbackUrl(route('iyzico.callback.wallet', [
                'conversation_id' => $conversation_id,
            ]));
            $request->setEnabledInstallments(array(2, 3, 6, 9));
            $buyer = new \Iyzipay\Model\Buyer();
            $buyer->setId(date('YmdHis'));
            $buyer->setName($user->first_name);
            $buyer->setSurname($user->last_name);
            $buyer->setGsmNumber($user->phone);
            $buyer->setEmail($user->email);
            $buyer->setIdentityNumber(date('YmdHis'));
            $buyer->setLastLoginDate(date('Y-m-d H:i:s'));
            $buyer->setRegistrationDate($user->created_at);
            $buyer->setRegistrationAddress("Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
            $buyer->setIp("85.34.78.112");
            $buyer->setCity("Istanbul");
            $buyer->setCountry("Turkey");
            $buyer->setZipCode("34732");
            $request->setBuyer($buyer);
            $basket_items = [];
            $firstBasketItem = new \Iyzipay\Model\BasketItem();
            $firstBasketItem->setId($basket_id);
            $firstBasketItem->setName("Product Purchase");
            $firstBasketItem->setCategory1("Order");
            $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
            $firstBasketItem->setPrice($amount);
            $basket_items[0] = $firstBasketItem;
            $request->setBasketItems($basket_items); # make request
            $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($request, $options);

            session()->put('iyzico_token', $payWithIyzicoInitialize->getToken());
            return redirect($payWithIyzicoInitialize->getPayWithIyzicoPageUrl());
        } catch (\Exception $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }
    public function retrieveIyzico(Request $request)
    {
        $options = new \Iyzipay\Options();
        $options->setApiKey(settingHelper('iyzico_api_key'));
        $options->setSecretKey(settingHelper('iyzico_secret_key'));

        if (settingHelper('is_iyzico_sandbox_mode') == 1) {
            $options->setBaseUrl("https://sandbox-api.iyzipay.com");
        } else {
            $options->setBaseUrl("https://api.iyzipay.com");
        }

        $iyzico_request = new \Iyzipay\Request\RetrievePayWithIyzicoRequest();
        if (settingHelper('default_language') == 'tr') {
            $iyzico_request->setLocale(\Iyzipay\Model\Locale::TR);
        } else {
            $iyzico_request->setLocale(\Iyzipay\Model\Locale::EN);
        }
        $iyzico_request->setConversationId($request->conversation_id);
        $iyzico_request->setToken(session()->get('iyzico_token'));

        return \Iyzipay\Model\PayWithIyzico::retrieve($iyzico_request, $options);
    }

    public function telrRedirect(Request $request, OrderInterface $order)
    {
        $paymentController = new paymentController($order);
        $data                       = $request->all();
        $request['payment_type']    = 'telr';
        $request['payment_mode']    = 'wallet_recharge';
        $success_url                = $paymentController->successUrl($request);

        $params = [
            'ivp_method'  => 'create',
            'ivp_store'   => settingHelper('telr_store_id'),
            'ivp_authkey' => settingHelper('telr_auth_key'),
            'ivp_cart'    => rand(),
            'ivp_test'    => '1',
            'ivp_amount'  => round($request->amount, 2),
            'ivp_currency' => 'AED',
            'ivp_desc'    => 'Recharge Wallet',
            'return_auth' => $success_url,
            'return_can'  => $paymentController->cancelUrl($request),
            'return_decl' => $paymentController->cancelUrl($request)
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://secure.telr.com/gateway/order.json");
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $results = curl_exec($ch);
        curl_close($ch);
        $results = json_decode($results, true);
        $ref = isset($results['order']) ? trim($results['order']['ref']) : '';
        $url = isset($results['order']) ? trim($results['order']['url']) : '';
        if (empty($ref) || empty($url)) {
            return back()->with(['error' => __('failed_to_create_telr')]);
        } else {
            return redirect($url);
        }
    }

    protected function getCurrency()
    {
        $currency = new CurrencyRepository();
        return $currency->currencyByCode('BDT');
    }
}
