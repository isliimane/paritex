<?php

namespace App\Http\Requests\Admin\PaymentGateway;

use Illuminate\Foundation\Http\FormRequest;

class PaymentGatewayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'paypal_client_id'           => 'required_if:payment_method,paypal',

            'stripe_key'                 => 'required_if:payment_method,stripe',
            'stripe_secret'              => 'required_if:payment_method,stripe',
        ];
    }
}
