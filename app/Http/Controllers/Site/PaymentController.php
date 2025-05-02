<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Models\Country;
use App\Repositories\Admin\Addon\PackageRepository;
use App\Repositories\Admin\CurrencyRepository;
use App\Repositories\Interfaces\Admin\Addon\WalletInterface;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Traits\ApiReturnFormatTrait;
use App\Traits\HomePage;
use App\Traits\PaymentTrait;
use App\Traits\SendNotification;
use App\Utility\AppSettingUtility;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use MercadoPago\Item;
use MercadoPago\Payer;
use MercadoPago\Preference;
use MercadoPago\SDK;
use Mollie\Api\Exceptions\ApiException;
use Obydul\LaraSkrill\SkrillClient;
use Obydul\LaraSkrill\SkrillRequest;
use Sentinel;
use Stripe\Stripe;
use Tymon\JWTAuth\Facades\JWTAuth;

class PaymentController extends Controller
{
    use HomePage, PaymentTrait, ApiReturnFormatTrait, SendNotification;

    public $order;

    public function __construct(OrderInterface $order)
    {
        $this->order = $order;
    }

    public function tokenGenerator($data): string
    {
        if (arrayCheck('type', $data) && $data['type'] == 'wallet') {
            $trx_id = Str::random();
        } else {
            $trx_id = arrayCheck('trx_id', $data) ? $data['trx_id'] : '';
        }
        return $trx_id;
    }

    public function codeGenerator($data): string
    {
        return arrayCheck('code', $data) && $data['code'] != 'undefined' ? $data['code'] : '';
    }

    public function findOrders($data)
    {
        if ($this->codeGenerator($data)) {
            $orders = $this->order->orderByCodes($data['code']);
        } else {
            $orders = $this->order->takePaymentOrder($this->tokenGenerator($data));
        }
        return $orders;
    }

    protected function apiToken($data): string
    {
        return arrayCheck('token', $data) ? $data['token'] : '';
    }

    public function successUrl($request, $user = null, $amount = null)
    {
        $token = $this->apiToken($request->all());

        if ($request->type == 'wallet' && $user) {
            $str = Str::random();
            $url = URL::temporarySignedRoute('recharge.wallet', now()->addMinutes(5), [
                'user_id' => $user->id,
                'total' => $amount,
                'transaction_id' => $str,
                'response' => 'yes',
                'payment_type' => $request->payment_type,
                'token' => $token
            ]);
        } else {
            if (authUser() || $token) {
                if ($request->payment_mode == 'api') {
                    $url = url("api/complete-order?trx_id=$request->trx_id&code=$request->code&payment_type=$request->payment_type&token=$token&curr=$request->curr&paymentID=$request->paymentID");
                } else {
                    $url = url("user/complete-order?trx_id=$request->trx_id&code=$request->code&payment_type=$request->payment_type&paymentID=$request->paymentID");
                }
            } else {
                if ($request->payment_mode == 'api') {
                    $url = url("api/complete-order?guest=1&trx_id=$request->trx_id&payment_type=$request->payment_type&curr=$request->curr&paymentID=$request->paymentID");
                } else {
                    $url = url("user/complete-order?guest=1&trx_id=$request->trx_id&payment_type=$request->payment_type&paymentID=$request->paymentID");
                }

            }
        }
        return $url;
    }

    public function cancelUrl($request)
    {
        if ($request->package_id) {
            $url = route('packages.purchase', $request->package_id);
        } else if ($request->type == 'wallet' || $request->payment_mode == 'wallet_recharge') {
            $url = url("my-wallet");
        } else {
            if ($request->payment_mode == 'api') {
                $url = url("api/complete-order?trx_id=$request->trx_id&code=$request->code&payment_type=$request->payment_type&token=$request->token&curr=$request->curr");
            } else {
                $url = url("payment");
            }
        }

        return $url;
    }

    public function findUser($data)
    {
        $user = null;
        if (arrayCheck('token', $data)) {
            try {
                $user = JWTAuth::parseToken()->authenticate();
            } catch (\Exception $e) {
                $user = '';
            }
        }
        if (!$user) {
            $user = authUser();
        } elseif (!authUser()) {
            $user = getWalkInCustomer();
        }
        return $user;
    }

    public function findSystemCountry(): string
    {
        $country = Country::find(settingHelper('default_country'));
        if ($country) {
            $region = $country->iso3;
        } else {
            $region = 'USA';
        }
        return $region;
    }

    public function findAmount($data, $orders = null, $active_currency = null)
    {
        $amount = 0;

        if (arrayCheck('type', $data) && $data['type'] == 'wallet') {
            $amount = $data['amount'];
        } else {
            if ($orders && count($orders) > 0) {
                if ($active_currency) {
                    $amount = $orders->sum('total_payable') * $active_currency->exchange_rate;
                } else {
                    $amount = $orders->sum('total_payable');
                }
            } elseif (arrayCheck('package_id', $data)) {
                $repo = new PackageRepository();
                $package = $repo->find($data['package_id']);
                $amount = $package->price;
            }
        }
        return $amount;
    }

    public function stripeRedirect(Request $request)
    {
        try {
            Stripe::setApiKey(settingHelper('stripe_secret'));
            $data = $request->all();
            $request['payment_type'] = 'stripe';
            $orders = $this->findOrders($data);
            $active_currency = $this->activeCurrencyCheck();
            $amount = $this->findAmount($data, $orders);
            $url = $this->successUrl($request, $this->findUser($data), $this->findAmount($data, $orders, $active_currency));
            if (($request->type != 'wallet' && count($orders) == 0) && !$request->package_id) {
                return back()->with(['error' => __('Oops.....Something Went Wrong')]);
            }
            $us = ['card']; //'alipay',  'us_bank_account', 'klarna'

                if ($active_currency && $active_currency->code == 'EUR') {
                    $stripe_currency = strtolower($active_currency->code);
                } elseif ($active_currency && $active_currency->code == 'INR') {
                    $stripe_currency = 'inr';
                } else {
                    $stripe_currency = 'usd';
                }
            

            if ($request->type == 'wallet') {
                if ($active_currency->code == 'USD' || $active_currency->code == 'EUR' || $active_currency->code == 'INR') {
                    $amount = round($amount * 100);
                } else {
                    $amount = round(($amount * 100) / $active_currency->exchange_rate);
                }
            } else {
                // $amount = $stripe_currency == 'eur' ? round(($amount*100) * $active_currency->exchange_rate) : round($amount*100);

                if ($stripe_currency == 'eur') {
                    $amount = round(($amount * 100) * $active_currency->exchange_rate);
                } elseif ($stripe_currency == 'inr') {
                    $amount = round(($amount * 100) * $active_currency->exchange_rate);
                } else {
                    $amount = round($amount * 100);
                }
            }

            $session_data    = [
                'payment_method_types'    => $us,
                'line_items'              => [
                    [
                        'price_data' => [
                            'currency'     => $stripe_currency,
                            'product_data' => [
                                'name' => 'Payment',
                            ],
                            'unit_amount'  => $amount,
                        ],
                        'quantity'   => 1,
                    ],
                ],
                'phone_number_collection' => [
                    'enabled' => 'true',
                ],
                'mode'                    => 'payment',
                'success_url'             => $url,
                'cancel_url'              => url()->previous(),
            ];
            $headers         = [
                'Authorization' => 'Basic '.base64_encode(settingHelper('stripe_secret').':'),
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ];

            $response        = httpRequest('https://api.stripe.com/v1/checkout/sessions', $session_data, $headers, true);
            session()->put('payment_intent', $response['payment_intent']);
            return redirect($response['url']);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function mollieRedirect(Request $request)
    {
        try {
            mollie()->setApiKey(settingHelper('mollie_api_key'));

            $data = $request->all();
            $request['payment_type'] = 'mollie';
            $orders = $this->findOrders($data);
            $url = $this->successUrl($request, authUser());
            $amount = $this->findAmount($data);

            if (count($orders) == 0 && !arrayCheck('package_id', $data)) {
                Toastr::error(__('Oops.....Something Went Wrong'), __('Error'));
                return back()->with(['error' => __('Oops.....Something Went Wrong')]);
            }

            $euro_exchange_rate = 1;
            $euro = AppSettingUtility::currencies()->where('code', 'EUR')->first();
            if ($euro):
                $euro_exchange_rate = $euro->exchange_rate;
            endif;

            $payment = mollie()->payments()->create([
                'amount' => [
                    'currency' => 'EUR', // Type of currency you want to send
                    'value' => number_format($amount * $euro_exchange_rate, 2, '.', ''), // You must send the correct number of decimals, thus we enforce the use of strings
                ],
                'description' => __('payment_by') . ' ' . settingHelper('system_name'),
                'redirectUrl' => $url, // after the payment completion where you to redirect
                //            "webhookUrl" => route('webhooks.mollie'),
                "metadata" => [
                    "order_id" => date('YmdHis'),
                ],
            ]);

            session()->put('id', $payment->id);// redirect customer to Mollie checkout page
            cache()->put('id', $payment->id);// redirect customer to Mollie checkout page

            return redirect($payment->getCheckoutUrl(), 303);
        } catch (\Exception $e) {

            Toastr::error($e->getMessage());
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function mollieSuccess(Request $request)
    {
        DB::beginTransaction();
        $user = authUser();


        $data = [
            'trx_id' => $request->trx,
            'payment_type' => 'mollie',
        ];

        if (!$user) {
            $user = getWalkInCustomer();
            $data['guest'] = 1;
        }

        if ($request->code) {
            $data['code'] = $request->code;
        }

        try {
            $this->order->completeOrder($data, authUser());
            $data = [
                'success' => __('Order Completed')
            ];

            DB::commit();

            if (request()->ajax()) {
                return response()->json($data);
            } else {
                if ($request->code) {
                    return redirect('get-invoice/' . $request->code);
                } else {
                    return redirect('invoice/' . session()->get('trx_id'));
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            session()->forget('trx_id');
            if (request()->ajax()) {
                return response()->json([
                    'error' => $e->getMessage()
                ]);
            } else {
                return redirect()->back()->with(['error' => $e->getMessage()]);
            }
        }
    }

    public function rechargeWithMollie(Request $request)
    {
        try {
            mollie()->setApiKey(settingHelper('mollie_api_key'));

            $data = $request->all();
            $request['payment_type'] = 'mollie';
            $active_currency = $this->activeCurrencyCheck($data);

            if (isset($data['curr'])) {
                $api_curr = AppSettingUtility::currencies()->where('code', $data['curr'])->first();
            }

            $euro = AppSettingUtility::currencies()->where('code', 'EUR')->first();
            if ($euro):
                $euro_exchange_rate = $euro->exchange_rate;
            endif;

            $base_currency_amount = $data['amount'] / $active_currency->exchange_rate;

            if (isset($data['token'])) {
                if ($data['curr'] != "EUR") {
                    $final_amount = $base_currency_amount * $euro_exchange_rate;
                } else {
                    $base_currency_amount = $data['amount'] / $api_curr->exchange_rate;
                    $final_amount = $data['amount'] * 1;
                }
            } else {
                if ($active_currency->code != "EUR") {
                    $final_amount = $base_currency_amount * $euro_exchange_rate;
                } else {
                    $final_amount = $data['amount'] * 1;
                }
            }

            $payment = mollie()->payments()->create([
                'amount' => [
                    'currency' => 'EUR', // Type of currency you want to send
                    'value' => number_format($final_amount, 2, '.', ''), // You must send the correct number of decimals, thus we enforce the use of strings
                ],
                'description' => __('Recharge') . ' ' . settingHelper('system_name'),
                'redirectUrl' => url($request->token ? 'mollie/recharge-success?amount=' . $base_currency_amount . '&token=' . $request->token : 'mollie/recharge-success?amount=' . $base_currency_amount), // after the payment completion where you to redirect
            ]);
            $payment = mollie()->payments()->get($payment->id);
            session()->put('id', $payment->id);// redirect customer to Mollie checkout page
            return redirect($payment->getCheckoutUrl(), 303);
        } catch (ApiException $e) {
            return back()->with(['error' => $e->getMessage()]);
        }
    }

    public function mollieRechargeSuccess(Request $request, WalletInterface $wallet)
    {
        $user = null;
        if ($request->token) {
            try {
                if (!$user = JWTAuth::parseToken()->authenticate()) {
                    return $this->responseWithError(__('unauthorized_user'), [], 401);
                }
            } catch (\Exception $e) {
                return $this->responseWithError(__('unauthorized_user'), [], 401);
            }
        }

        $data['payment_type'] = 'mollie';
        $data['amount'] = $request->amount;
        $source = 'wallet_recharge';
        $payment_details = $this->methodCheck($data, []);

        $userWallet['user_id'] = authId() ?: $user->id;
        $userWallet['order_id'] = null;
        $userWallet['amount'] = $data['amount'];
        $userWallet['source'] = $source;
        $userWallet['type'] = 'income';
        $userWallet['status'] = 'pending';
        $userWallet['image'] = array_key_exists('file', $data) ? $data['file'] : [];
        $userWallet['transaction_id'] = array_key_exists('transaction_id', $data) ? $data['transaction_id'] : null;
        $userWallet['payment_method'] = $data['payment_type'];
        $userWallet['payment_details'] = $payment_details;

        $wallet->customerBalanceStore($userWallet, 'wallet_recharge');

        $this->SendNotification(Sentinel::findById(1), "New Wallet Request Is Created.", 'success', "wallet/recharge-requests", '');

        return redirect('my-wallet');
    }

    public function jazzCash(): bool
    {
        return true;
    }

    public function sslResponse(Request $request)
    {
        try {
            $bdt_currency = $this->getCurrency();

            if (!$bdt_currency) {
                return false;
            }

            if (settingHelper('is_sslcommerz_sandbox_mode_activated') == 1) {
                config(['sslcommerz.apiDomain' => 'https://sandbox.sslcommerz.com']);
            } else {
                config(['sslcommerz.apiDomain' => 'https://securepay.sslcommerz.com']);
            }

            $data = $request->all();
            $request['payment_type'] = 'ssl_commerze';
            $orders = $this->findOrders($data);
            $active_currency = $this->activeCurrencyCheck();
            $user = $this->findUser($data);
            $url = '';
            $amount = $this->amountCalculator($orders, $data, $active_currency, $bdt_currency);
            $post_data['total_amount'] = round($amount['total_amount']);
            $db_amount = $amount['db_amount'];

            $post_data['currency'] = "BDT";
            $post_data['tran_id'] = date('YmdHis'); // tran_id must be unique
            # CUSTOMER INFORMATION
            $post_data['cus_name'] = 'cus_name';
            $post_data['cus_email'] = 'cus_email';
            $post_data['cus_add1'] = 'Customer Address';
            $post_data['cus_add2'] = "";
            $post_data['cus_city'] = "";
            $post_data['cus_state'] = "";
            $post_data['cus_postcode'] = "";
            $post_data['cus_country'] = "Bangladesh";
            $post_data['cus_phone'] = 'cus_phone';
            $post_data['cus_fax'] = "";
            # SHIPMENT INFORMATION
            $post_data['ship_name'] = "Store Test";
            $post_data['ship_add1'] = "Dhaka";
            $post_data['ship_add2'] = "Dhaka";
            $post_data['ship_city'] = "Dhaka";
            $post_data['ship_state'] = "Dhaka";
            $post_data['ship_postcode'] = "1000";
            $post_data['ship_phone'] = "";
            $post_data['ship_country'] = "Bangladesh";
            $post_data['shipping_method'] = "NO";
            $post_data['product_name'] = "Computer";
            $post_data['product_category'] = "Goods";
            $post_data['product_profile'] = "physical-goods";

            config(['sslcommerz.success_url' => str_replace(url('/'), '', $this->successUrl($request, $user, $db_amount))]);
            config(['sslcommerz.cancel_url' => str_replace(url('/'), '', $this->cancelUrl($request))]);

            config(['sslcommerz.apiCredentials.store_id' => settingHelper('sslcommerz_id')]);
            config(['sslcommerz.apiCredentials.store_password' => settingHelper('sslcommerz_password')]);

            $sslc = new SslCommerzNotification();

            $response = $sslc->makePayment($post_data);

            if ($response) {
                $data = json_decode($response);
                $url = $data->data;
            }
            if ($url) {
                return redirect($url);
            } else {

                return back()->with(['error' => __('Ops..!')]);
            }

        } catch (\Exception $e) {

            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

    public function mercadoPago(Request $request)
    {
        $access_key = settingHelper('mercadopago_access_key');

        if (!$access_key) {
            return back()->with(['warning' => __('provide_correct_credential')]);
        }

        $data = $request->all();
        $request['payment_type'] = 'mercadopago';
        $orders = $this->findOrders($data);
        $success_url = $this->successUrl($request);
        $active_currency = $this->activeCurrencyCheck($data);
        $amount = $this->findAmount($data, $orders, $active_currency);

        $billing_details = [
            'name' => '',
            'postal_code' => '',
        ];


        if (count($orders) > 0) {
            $order = $orders->first();

            if (!$order->pickup_hub_id && $order->billing_address && count($order->billing_address) > 0) {
                $billing_details = $order->billing_address;
            }
        }

        SDK::setAccessToken(settingHelper('mercadopago_access_key'));

        $preference = new Preference();

        $payer = new Payer();
        $payer->name = authUser()->full_name;
        $payer->email = authUser()->email;
        $payer->phone = array(
            "area_code" => $billing_details['postal_code'],
            "number" => authUser()->phone
        );

        // Crea un ítem en la preferencia

        $item = new Item();
        $item->title = $billing_details['name'];
        $item->quantity = 1;

        $item->unit_price = $amount;
        $preference->payer = $payer;
        $preference->items = array($item);

        $preference->back_urls = array(
            "success" => $success_url,
            "failure" => url()->previous(),
            "pending" => url()->previous(),
        );

        $preference->save();

        return view('frontend.payments.mercado_pago', compact('preference'));
    }

    public function telrRedirect(Request $request)
    {
        $data = $request->all();
        $request['payment_type'] = 'telr';
        $orders = $this->findOrders($data);
        $success_url = $this->successUrl($request);

        $params = [
            'ivp_method' => 'create',
            'ivp_store' => settingHelper('telr_store_id'),
            'ivp_authkey' => settingHelper('telr_auth_key'),
            'ivp_cart' => rand(),
            'ivp_test' => '1',
            'ivp_amount' => round($orders->sum('total_payable'), 2),
            'ivp_currency' => 'AED',
            'ivp_desc' => 'Order Processes',
            'return_auth' => $success_url,
            'return_can' => $this->cancelUrl($request),
            'return_decl' => $this->cancelUrl($request)
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


    public function skrillRedirect(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->all();
        $request['payment_type'] = 'skrill';
        $orders = $this->findOrders($data);
        $active_currency = $this->activeCurrencyCheck($data);
        $trx_id = $this->tokenGenerator($data);
        $amount = $this->findAmount($data, $orders, $active_currency);
        $success_url = $this->successUrl($request, $this->findUser($data), $amount);
        $logo = settingHelper('dark_logo') != [] && @is_file_exists(settingHelper('dark_logo')['original_image']) ? get_media(@settingHelper('dark_logo')['original_image'], @settingHelper('dark_logo')['storage']) : static_asset('images/default/dark-logo.png');

        $skrilRequest = new SkrillRequest();
        $skrilRequest->pay_to_email = settingHelper('skrill_merchant_email'); // your merchant email
        $skrilRequest->return_url = $success_url;
        $skrilRequest->cancel_url = url()->previous();
        $skrilRequest->logo_url = $logo;  // optional
        $skrilRequest->status_url = $success_url;
        $skrilRequest->amount = $amount;
        $skrilRequest->currency = $active_currency->code;
        $skrilRequest->language = 'EN';
        $skrilRequest->country = $this->findSystemCountry();
        $skrilRequest->prepare_only = '1';
        $skrilRequest->merchant_fields = 'site_name, customer_email';
        $skrilRequest->site_name = settingHelper('system_name');
        $skrilRequest->customer_email = authUser()->email;
        $skrilRequest->detail1_description = "Product Sale for $trx_id";
        $skrilRequest->detail1_text = "Product Sale for $trx_id";
        $skrilRequest->transaction_id = date('YmdHis');

        $client = new SkrillClient($skrilRequest);
        $sid = $client->generateSID(); //return SESSION ID
        $jsonSID = json_decode($sid);

        if ($jsonSID != null && $jsonSID->code == "BAD_REQUEST")
            return $jsonSID->message;

        $redirectUrl = $client->paymentRedirectUrl($sid); //return redirect url
        return redirect()->to($redirectUrl);
    }

    public function iyzicoRedirect(Request $request)
    {

        try {
            if (settingHelper('is_iyzico_sandbox_enabled')) {
                $url = "https://sandbox-api.iyzipay.com";
            } else {
                $url = "https://api.iyzipay.com";
            }
            $data = $request->all();
            $orders = $this->findOrders($data);
            $user = $this->findUser($data);
            $amount = round($orders->sum('total_payable'), 2);
            $basket_id = date('YmdHis');
            $shipping_address = '';
            $billing_address = '';
            $conversation_id = rand(100000000, 999999999);

            if (count($orders) > 0) {
                $shipping_address = $orders->first()->shipping_address;
                $billing_address = $orders->first()->billing_address;
            }
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
            $request->setCallbackUrl(route('iyzico.callback', [
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
            $request->setShippingAddress($this->getAddress($shipping_address));
            $request->setBillingAddress($this->getAddress($billing_address));
            $request->setBasketItems($basket_items);# make request
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

    public function getAddress($billing_address): \Iyzipay\Model\Address
    {
        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName($billing_address ? $billing_address['name'] : 'Yoori Customer');
        $billingAddress->setCity($billing_address ? $billing_address['city'] : 'Istanbul');
        $billingAddress->setCountry($billing_address ? $billing_address['country'] : "Turkey");
        $billingAddress->setAddress($billing_address ? $billing_address['address'] : "Nidakule Göztepe, Merdivenköy Mah. Bora Sok. No:1");
        $billingAddress->setZipCode($billing_address ? $billing_address['postal_code'] : "34742");
        return $billingAddress;
    }

}
