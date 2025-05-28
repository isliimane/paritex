<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Country;
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
        if ($request->type == 'wallet' || $request->payment_mode == 'wallet_recharge') {
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
            if (($request->type != 'wallet' && count($orders) == 0)) {
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
