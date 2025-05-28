@extends('admin.partials.master')
@section('payment-gateway')
    active
@endsection
@section('payment-gateway')
    active
@endsection
@section('title')
    {{ __('Payment-Gateway') }}
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <h2 class="section-title">{{ __('Payment Methods') }} (19)</h2>
            <div class="row">
                <div class="col-12 col-sm-12 col-md-4 col-lg-3">
                    <div class="card payment_method">
                        <div class="card-body">
                            <ul class="nav nav-pills flex-column" id="myTab4" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ old('payment_method') == '' || old('payment_method') == 'paypal' ? 'active' : ''}}"
                                       id="paypal-tab" data-toggle="tab" href="#paypal" role="tab"
                                       aria-controls="contact" aria-selected="false">{{ __('Paypal') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ old('payment_method') == 'stripe' ? 'active' : '' }}"
                                       id="stripe-tab" data-toggle="tab" href="#stripe" role="tab"
                                       aria-controls="contact" aria-selected="false">{{ __('Stripe') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ old('payment_method') == 'google_pay' ? 'active' : ''}}"
                                       id="google_pay-tab" data-toggle="tab" href="#google_pay" role="tab"
                                       aria-controls="profile" aria-selected="false">{{ __('google_pay') }}</a>
                                </li>
                                
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-8 col-lg-9">
                    <div class="tab-content no-padding" id="myTab2Content">
                        <div class="tab-pane fade {{ old('payment_method') == '' || old('payment_method') == 'paypal' ? 'show active' : ''}}"
                             id="paypal" role="tabpane1" aria-labelledby="paypal-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ __('Paypal Setting') }}</h4>
                                </div>
                                <div class="card-body col-md-10 middle">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-6 pl-0">
                                                <div class="form-group">
                                                    <label
                                                            class="custom-switch mt-2 {{ hasPermission('payment_gateway_update') ? '' : 'cursor-not-allowed' }}">
                                                        <input type="checkbox"
                                                               name="custom-switch-checkbox"
                                                               value="admin-payment-status-change/{{ 'is_paypal_activated' }}"
                                                               {{ hasPermission('payment_gateway_update') ? '' : 'disabled' }}
                                                               class="{{ hasPermission('payment_gateway_update') ? 'status-change' : '' }} custom-switch-input " {{ settingHelper('is_paypal_activated') == 1 ? 'checked' : ''}} />
                                                        <span class="custom-switch-indicator"></span>
                                                        <span
                                                                class="custom-switch-description">{{ __('Activate') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(hasPermission('payment_gateway_update'))
                                        <form action="{{ route('payment.gateway.update') }}" method="post"
                                              enctype="multipart/form-data">
                                            @method('put')
                                            @csrf
                                            @endif
                                            <div class="form-group">
                                                <label for="paypal_client_id">{{ __('Client ID ') }}</label>
                                                <input type="hidden" name="payment_method" value="paypal">
                                                <input type="text" class="form-control" name="paypal_client_id"
                                                       id="paypal_client_id"
                                                       value="{{ old('paypal_client_id') ? old('paypal_client_id') : settingHelper('paypal_client_id') }}"
                                                       placeholder="{{ __('Paypal Client ID') }}">
                                                @if ($errors->has('paypal_client_id'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('paypal_client_id') }}
                                                    </div>
                                                @endif
                                            </div>
                                            @if(hasPermission('payment_gateway_update'))
                                                <div class="text-md-right">
                                                    <button class="btn btn-outline-primary">{{ __('Save') }}</button>
                                                </div>
                                            @endif
                                            @if(hasPermission('payment_gateway_update'))
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ old('payment_method') == 'stripe' ? 'show active' : '' }}"
                             id="stripe" role="tab" aria-labelledby="stripe-tab">
                            <div class="card">
                                <div class="card-header extra-padding">
                                    <h4>{{ __('Stripe Setting') }}</h4>
                                </div>
                                <div class="card-body col-md-10 middle">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-6 pl-0">
                                                <div class="form-group">
                                                    <label
                                                            class="custom-switch mt-2 {{ hasPermission('payment_gateway_update') ? '' : 'cursor-not-allowed' }}">
                                                        <input type="checkbox"
                                                               name="custom-switch-checkbox"
                                                               value="admin-payment-status-change/{{ 'is_stripe_activated' }}"
                                                               {{ hasPermission('payment_gateway_update') ? '' : 'disabled' }}
                                                               class=" {{ hasPermission('payment_gateway_update') ? 'status-change' : '' }} custom-switch-input " {{ settingHelper('is_stripe_activated') == 1 ? 'checked' : ''}} />
                                                        <span class="custom-switch-indicator"></span>
                                                        <span
                                                                class="custom-switch-description">{{ __('Activate') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(hasPermission('payment_gateway_update'))
                                        <form action="{{ route('payment.gateway.update') }}" method="post"
                                              enctype="multipart/form-data">
                                            @method('put')
                                            @csrf
                                            @endif
                                            <div class="form-group">
                                                <label for="stripe_key">{{ __('Stripe Key') }}</label>
                                                <input type="hidden" name="payment_method" value="stripe">
                                                <input type="text" class="form-control" name="stripe_key"
                                                       id="stripe_key"
                                                       value="{{ old('stripe_key') ? old('stripe_key') : settingHelper('stripe_key') }}"
                                                       placeholder="{{ __('Stripe Client Key') }}">
                                                @if ($errors->has('stripe_key'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('stripe_key') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="stripe_secret">{{ __('Stripe Secret') }}</label>
                                                <input type="text" class="form-control" id="stripe_secret"
                                                       name="stripe_secret"
                                                       value="{{ old('stripe_secret') ? old('stripe_secret') : settingHelper('stripe_secret') }}"
                                                       placeholder="{{ __('Stripe Client Secret') }}">
                                                @if ($errors->has('stripe_secret'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('stripe_secret') }}
                                                    </div>
                                                @endif
                                            </div>
                                            @if(hasPermission('payment_gateway_update'))
                                                <div class="text-md-right">
                                                    <button class="btn btn-outline-primary">{{ __('Save') }}</button>
                                                </div>
                                            @endif
                                            @if(hasPermission('payment_gateway_update'))
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ old('payment_method') == 'google_pay' ? 'show active' : ''}}"
                             id="google_pay" role="tab" aria-labelledby="mid_trans-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ __('google_pay_setting') }}</h4>
                                </div>
                                <div class="card-body col-md-10 middle">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-6 pl-0">
                                                <div class="form-group">
                                                    <label
                                                            class="custom-switch mt-2 {{ hasPermission('payment_gateway_update') ? '' : 'cursor-not-allowed' }}">
                                                        <input type="checkbox"
                                                               name="custom-switch-checkbox"
                                                               value="admin-payment-status-change/is_google_pay_activated"
                                                               {{ hasPermission('payment_gateway_update') ? '' : 'disabled' }}
                                                               class="{{ hasPermission('payment_gateway_update') ? 'status-change' : '' }} custom-switch-input " {{ settingHelper('is_google_pay_activated') == 1 ? 'checked' : ''}} />
                                                        <span class="custom-switch-indicator"></span>
                                                        <span
                                                                class="custom-switch-description">{{ __('Activate') }}</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(hasPermission('payment_gateway_update'))
                                        <form method="post" action="{{ route('payment.gateway.update') }}"
                                              enctype="multipart/form-data">
                                            @method('put')
                                            @csrf
                                            @endif
                                            <div class="form-group">
                                                <label for="google_pay_merchant_name">{{ __('merchant_name') }}</label>
                                                <input type="hidden" name="payment_method" value="google_pay">
                                                <input type="text" class="form-control" id="google_pay_merchant_name"
                                                       name="google_pay_merchant_name"
                                                       value="{{ old('google_pay_merchant_name') ? : settingHelper('google_pay_merchant_name') }}"
                                                       placeholder="{{ __('merchant_name') }}">
                                                @if ($errors->has('google_pay_merchant_name'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('google_pay_merchant_name') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="google_pay_merchant_id">{{ __('merchant_id') }}</label>
                                                <input type="text" class="form-control" id="google_pay_merchant_id"
                                                       name="google_pay_merchant_id"
                                                       value="{{ old('google_pay_merchant_id') ? : settingHelper('google_pay_merchant_id') }}"
                                                       placeholder="{{ __('merchant_id') }}">
                                                @if ($errors->has('google_pay_merchant_id'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('google_pay_merchant_id') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="google_pay_gateway">{{ __('gateway') }}</label>
                                                <input type="text" class="form-control" id="google_pay_gateway"
                                                       name="google_pay_gateway"
                                                       value="{{ old('google_pay_gateway') ? : settingHelper('google_pay_gateway') }}"
                                                       placeholder="{{ __('gateway') }}">
                                                @if ($errors->has('google_pay_gateway'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('google_pay_gateway') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="form-group">
                                                <label for="google_pay_gateway_merchant_id">{{ __('gateway_merchant_id') }}</label>
                                                <input type="text" class="form-control"
                                                       id="google_pay_gateway_merchant_id"
                                                       name="google_pay_gateway_merchant_id"
                                                       value="{{ old('google_pay_gateway_merchant_id') ? : settingHelper('google_pay_gateway_merchant_id') }}"
                                                       placeholder="{{ __('gateway_merchant_id') }}">
                                                @if ($errors->has('google_pay_gateway_merchant_id'))
                                                    <div class="invalid-feedback">
                                                        {{ $errors->first('google_pay_gateway_merchant_id') }}
                                                    </div>
                                                @endif
                                            </div>
                                            @if(hasPermission('payment_gateway_update'))
                                                <div class="text-md-right">
                                                    <button class="btn btn-outline-primary">{{ __('Save') }}</button>
                                                </div>
                                            @endif
                                            @if(hasPermission('payment_gateway_update'))
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

