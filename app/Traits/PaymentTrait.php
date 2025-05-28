<?php

namespace App\Traits;

use App\Repositories\Admin\CurrencyRepository;
use App\Utility\AppSettingUtility;
use Brian2694\Toastr\Facades\Toastr;
use Stripe\Charge;
use Stripe\Stripe;

trait PaymentTrait
{

    public function methodCheck($data, $orders = [], $user = null)
    {
        $currency = new CurrencyRepository();

        if (session()->has('currency')) {
            $user_currency = session()->get('currency');
        } else {
            $user_currency = settingHelper('default_currency');
        }

        $active_currency = $currency->get($user_currency);

        if ($data['payment_type'] == 'paypal') {
            return $this->paypal($data);
        } elseif ($data['payment_type'] == 'stripe') {
            return $this->stripe();
        }
        return false;
    }

    public function getCurrency($code= 'BDT')
    {
        $currency = new CurrencyRepository();
        return $currency->currencyByCode($code);
    }

    public function activeCurrencyCheck()
    {
        $currency = new CurrencyRepository();

        $user_currency  = currencyCheck();

        if (settingHelper('default_currency'))
        {
            $default_currency           = settingHelper('default_currency');
        }
        else{
            $default_currency = 1;
        }

        return $currency->get($user_currency) ? : $currency->get($default_currency);
    }

    public function amountCalculator($orders=null,$data,$active_currency,$which_currency): array
    {
        $amount = $orders && count($orders) > 0 ? $orders->sum('total_payable') : $data['amount'];


        $rate = $amount/$active_currency->exchange_rate;

        $db_amount = 0;
        if (arrayCheck('type', $data) && $data['type'] == 'wallet')
        {
            if ($active_currency->code != $which_currency->code)
            {
                $total_amount = round($rate * $which_currency->exchange_rate);
                $db_amount = $rate;
            }
            else{
                $total_amount = $amount;
                $db_amount = $amount/$which_currency->exchange_rate;
            }
        }
        else{
            if ($active_currency->code != $which_currency->code)
            {
                $total_amount = $rate * $which_currency->exchange_rate;
            }
            else{
                $total_amount = $amount * $which_currency->exchange_rate;
            }
        }

        return [
            'db_amount'     => $db_amount,
            'total_amount'  => $total_amount,
        ];
    }

    protected function paypal($data): array
    {
        $result = [];

        if (array_key_exists('paymentSource', $data) && $data['paymentSource'] == 'card') {
            $payer = @$data['order']['payer'];
            if ($payer) {
                $result = [
                    'name' => $payer['name']['given_name'] . ' ' . $payer['name']['surname'],
                    'email' => $payer['email_address'],
                    'link' => $data['order']['links'][0]['href']
                ];
            }
        }

        return $result;
    }

    protected function stripe(): array
    {
        $payment_details = [];

        if (! session()->get('payment_intent')) {
            $payment_details['status'] = 'failed';

            return $payment_details;
        }
        $headers         = [
            'Authorization' => 'Basic '.base64_encode(settingHelper('stripe_secret').':'),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];
        $url             = 'https://api.stripe.com/v1/charges';
        $fields          = [
            'payment_intent' => session()->get('payment_intent'),
            'limit'          => '1',
        ];
        $charge          = httpRequest($url, $fields, $headers, true, 'GET', false);

        if (count($charge['data']) > 0) {
            $payment         = $charge['data'][0];

            $payment_details = [
                'payment_intent' => $payment['payment_intent'],
                'customer'       => $payment['customer'],
                'email'          => @$payment['billing_details']['email'],
                'name'           => @$payment['billing_details']['name'],
                'receipt'        => $payment['receipt_url'],
                'status'         => 'paid',
            ];

            $payment_method  = $payment['payment_method_details']['type'];

            if ($payment_method == 'card') {
                $payment_details['card']  = $payment['payment_method_details']['card']['brand'];
                $payment_details['last4'] = $payment['payment_method_details']['card']['last4'];
            }
            if ($payment_method == 'alipay') {
                $payment                           = $payment['payment_method_details'];
                $payment_details['fingerprint']    = $payment['alipay']['fingerprint'];
                $payment_details['transaction_id'] = $payment['alipay']['transaction_id'];
            }
            if ($payment_method == 'klarna') {
                $payment                                    = $payment['payment_method_details'];
                $payment_details['payment_method_category'] = $payment['klarna']['payment_method_category'];
                $payment_details['preferred_locale']        = $payment['klarna']['preferred_locale'];
            }
            if ($payment_method == 'us_bank_account') {
                $payment                                = $payment['payment_method_details'];

                $payment_details['account_holder_type'] = $payment['us_bank_account']['account_holder_type'];
                $payment_details['account_type']        = $payment['us_bank_account']['account_type'];
                $payment_details['bank_name']           = $payment['us_bank_account']['bank_name'];
                $payment_details['fingerprint']         = $payment['us_bank_account']['fingerprint'];
                $payment_details['last4']               = $payment['us_bank_account']['last4'];
                $payment_details['routing_number']      = $payment['us_bank_account']['routing_number'];
            }
            if ($payment_method == 'bancontact' || $payment_method == 'sofort') {
                $payment                                         = $payment['payment_method_details'];

                $type                                            = $payment_method == 'bancontact' ? $payment['bancontact'] : $payment['sofort'];

                $payment_details['bank_code']                    = $type['bank_code'];
                $payment_details['bank_name']                    = $type['bank_name'];
                $payment_details['country']                      = getArrayValue('country', $type);
                $payment_details['bic']                          = $type['bic'];
                $payment_details['generated_sepa_debit']         = $type['generated_sepa_debit'];
                $payment_details['generated_sepa_debit_mandate'] = $type['generated_sepa_debit_mandate'];
                $payment_details['iban_last4']                   = $type['iban_last4'];
                $payment_details['verified_name']                = $type['verified_name'];
            }
            if ($payment_method == 'sepa_debit') {
                $payment                        = $payment['payment_method_details'];
                $payment_details['bank_code']   = $payment['sepa_debit']['bank_code'];
                $payment_details['branch_code'] = $payment['sepa_debit']['branch_code'];
                $payment_details['country']     = $payment['sepa_debit']['country'];
                $payment_details['fingerprint'] = $payment['sepa_debit']['fingerprint'];
                $payment_details['last4']       = $payment['sepa_debit']['last4'];
                $payment_details['mandate']     = $payment['sepa_debit']['mandate'];
            }
            if ($payment_method == 'ideal') {
                $payment                                         = $payment['payment_method_details'];

                $payment_details['bank']                         = $payment['ideal']['bank'];
                $payment_details['bic']                          = $payment['ideal']['bic'];
                $payment_details['generated_sepa_debit']         = $payment['ideal']['generated_sepa_debit'];
                $payment_details['generated_sepa_debit_mandate'] = $payment['ideal']['generated_sepa_debit_mandate'];
                $payment_details['iban_last4']                   = $payment['ideal']['iban_last4'];
                $payment_details['verified_name']                = $payment['ideal']['verified_name'];
            }
            if ($payment_method == 'p24' || $payment_method == 'eps') {
                $payment                          = $payment['payment_method_details'];
                $type                             = $payment_method == 'p24' ? $payment['p24'] : $payment['eps'];

                $payment_details['bank']          = $type['bank'];
                $payment_details['reference']     = getArrayValue('reference', $type);
                $payment_details['verified_name'] = $type['verified_name'];
            }
        }

        session()->forget('payment_intent');

        return $payment_details;
    }

}
