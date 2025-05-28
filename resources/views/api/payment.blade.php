<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('Payment') }}</title>

    <link rel="stylesheet" href="{{ static_asset('frontend/css/materialdesignicons.min.css') }}?version=130">
    <link rel="stylesheet" href="{{ static_asset('admin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('frontend/css/main.css') }}?{{ date('YmdHis') }}">
    <link rel="stylesheet" href="{{ static_asset('frontend/css/development.css') }}">
    <link rel="stylesheet" href="{{ static_asset('frontend/css/responsive.css') }}">
    <link rel="stylesheet" href="{{ static_asset('frontend/css/yassine-responsive.css') }}">
    <link rel="stylesheet" href="{{ static_asset('admin/css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ static_asset('frontend/css/vue-toastr-2.min.css') }}">

    <style>
        :root {
            --primary-color: {{ settingHelper('primary_color')}};
            --menu-active-color: {{ settingHelper('menu_active_color') }};
            --menu-bg-color: {{ settingHelper('menu_background_color') }};
            --menu-text-color: {{ settingHelper('menu_text_color') }};
            --primary-font: '{{primaryFont()}}', sans-serif;
            --profile-sidebar: {{ settingHelper('menu_background_color').'10' }};
            --sidebar-base-color: {{ settingHelper('primary_color').'20'}};
            --btn-bg-color: {{ settingHelper('button_background_color') }};
            --btn-txt-color: {{ settingHelper('button_text_color') }};
            --btn-bdr-color: {{ settingHelper('button_border_color') }};
        }

        @if(settingHelper('full_width_menu_background') !='1' && settingHelper('header_theme') == 'header_theme1')
               .header-menu.header_theme1 {
            background-color: transparent !important;
        }

        @endif
        @if(base64_decode(settingHelper('custom_css')))
            {{ base64_decode(settingHelper('custom_css')) }}
        @endif
            @if(settingHelper('is_tawk_messenger_activated') == 1)
        .fb_dialog_content iframe {
            margin-right: 90px !important;
        }
        @endif
    </style>
</head>
<body>

<section class="shopping-cart api">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-md-5">
                <div class="sg-shipping">
                    <div class="card-list">
                        <ul class="global-list grid-3">
                            @if(settingHelper('is_paypal_activated') == 1)
                                <li>
                                    <div class="input-checkbox">
                                        <input type="radio" value="paypal"
                                               id="paypal" name="payment">
                                        <label for="paypal">
                                            <img src="{{ url('public/images/payment-method/paypal.svg') }}" alt="paypal"
                                                 class="img-fluid">
                                            {{ __('pay_with_payPal') }}
                                        </label>
                                    </div>
                                </li>
                            @endif
                         
                            @if(settingHelper('is_stripe_activated') == 1)
                                <li>
                                    <div class="input-checkbox">
                                        <input type="radio" id="stripe" value="stripe"
                                               name="payment">
                                        <label for="stripe">
                                            <img src="{{ url('public/images/payment-method/stripe.svg') }}" alt="stripe"
                                                 class="img-fluid">
                                            {{ __('pay_with_stripe') }}
                                        </label>
                                    </div>
                                </li>
                            @endif
                           

                            @if(!$check_cod)
                                <li>
                                    <div class="input-checkbox">
                                        <input type="radio" id="cash"
                                               value="cash_on_delivery" name="payment">
                                        <label for="cash">
                                            <img src="{{ url('public/images/payment-method/cash.svg') }}" alt="cash"
                                                 class="img-fluid">
                                            {{ __('cash_on_delivery') }}
                                        </label>
                                    </div>
                                </li>
                            @endif

                            @if(in_array('offline_payment',$addons))
                                @foreach($offline_methods as $offline)
                                    <li>
                                        <div class="input-checkbox">
                                            <input type="radio" data-id="{{ $offline->id }}"
                                                   data-value="{{$offline->getTranslation('name',app()->getLocale())}}"
                                                   data-instructions="{{ $offline->getTranslation('instructions',app()->getLocale()) }}"
                                                   id="offline_{{$offline->id}}"
                                                   value="offline_method" name="payment">
                                            <label for="offline_{{$offline->id}}">
                                                <img src="{{ $offline->image }}"
                                                     alt="{{ $offline->getTranslation('name',app()->getLocale()) }}"
                                                     class="img-fluid">
                                                {{ $offline->getTranslation('name',app()->getLocale()) }}
                                            </label>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        @if(authUser() && authUser()->balance >= $orders->sum('total_payable') && settingHelper('wallet_system') == 1)
                            <div class="row text-center">
                                <div class="col-lg-12">
                                    <div class="separator mb-3">
                                    <span class="bg-white px-3">
                                        <span class="opacity-60">{{ __('or') }}</span>
                                    </span>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <p>{{ __('your_wallet_balance') }}
                                        : {{ get_price(authUser()->balance,$currency) }}</p>
                                    <button type="button" id="wallet_submit" data-type="wallet"
                                            class="btn btn-primary form_submit">{{ __('pay_with_wallet') }}</button>
                                    <button class="btn btn-primary d-none loading" type="button" disabled>
                                        <span class="spinner-border spinner-border-sm" role="status"
                                              aria-hidden="true"></span>
                                        <span class="sr-only"></span>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div><!-- /.row -->
        <div class="row">
            <div class="col-lg-4 pl-lg-5">
                <div class="order-summary paymentCustom">
                    <div class="peymentToggler">
                        <div class="paymentHeader showHide">
                            <h6>{{ __('price_details') }}</h6>

                            <div class="sg-card">
                                @php
                                    $action_url = authUser() ? url('api/complete-order') : url('api/complete-order?&guest=1');
                                @endphp
                                <ul class="global-list">
                                    <li>{{ __('subtotal') }}
                                        <span>{{ get_price($orders->sum('sub_total'),$currency) }}</span>
                                    </li>
                                    <li>{{ __('tax') }}
                                        <span>{{ get_price($orders->sum('total_tax'),$currency) }}</span>
                                    </li>
                                    <li>{{ __('discount') }}
                                        <span>{{ get_price($orders->sum('discount'),$currency) }}</span></li>
                                    <li>{{ __('shipping_cost') }}
                                        <span>{{ get_price($orders->sum('shipping_cost'),$currency) }}</span></li>
                                    @if(settingHelper('coupon_system') == 1)
                                        <li>{{ __('coupon_discount') }}
                                            <span>{{ get_price($orders->sum('coupon_discount'),$currency) }}</span></li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <div class="paymentBTN-group">
                            <div class="order-total sm-fixed-bottom">
                                <p>{{ __('total') }}<span id="iconHi"><span
                                                class="icon mdi mdi-name mdi-chevron-down"></span></span>
                                    <span>{{ get_price($orders->sum('total_payable'),$currency) }}</span></p>

                                <a href="javascript:void(0)"
                                   class="btn btn-primary paymentBTNFixed disable_btn">{{ __('pay_now') }}</a>

                                <div class="div_btns d-none">
                                    <a href="{{ url("stripe/redirect?trx_id=$trx_id&payment_mode=api&code=$code&token=$token&curr=$currency")  }}"
                                       class="btn btn-primary paymentBTNFixed payment_btns d-none stripe_btn"> {{ __('pay_now') }}</a>



                                    <button id="cod_n_pay_later_submit" type="button"
                                            class="btn btn-primary paymentBTNFixed d-none payment_btns confirm_btn form_submit">{{ __('confirm') }}</button>

                                    <button class="btn btn-primary paymentBTNFixed d-none loading" type="button"
                                            disabled>
                                        <span class="spinner-border spinner-border-sm" role="status"
                                              aria-hidden="true"></span>
                                        <span class="sr-only"></span>
                                    </button>

                                    <a href="javascript:void(0)"
                                       class="btn btn-primary paymentBTNFixed payment_btns d-none offline_method_btn"
                                       data-toggle="modal" data-target="#offline"> {{ __('pay_with') }}
                                        <span></span></a>
                                </div>

                                <div class="mx-auto w_40 payment_btns d-none paypal_btn"
                                     id="paypal-button-container"></div>

                                <form action="{{ $action_url }}" method="post">@csrf
                                    <input type="hidden" name="trx_id" value="{{ $trx_id }}">
                                    <input type="hidden" name="code" value="{{ $code }}">
                                    <input type="hidden" name="token" value="{{ $token }}">
                                    <input type="hidden" name="amount" value="{{ $orders->sum('total_payable') }}">
                                </form>
                            </div>
                        </div>
                    </div>


                    <div class="sm-content-show"></div>


                </div>
            </div>
        </div>
    </div>
</section><!-- /.shopping-cart -->

<!--offline -->
<div class="modal fade" id="offline" tabindex="-1" aria-labelledby="offline_modal"
     aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('pay_with') }} <span></span></h5>
                <button type="button" class="close modal_close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="offline_form" id="offline_form">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>{{ __('Upload File') }}</label>
                                <div class="input-group">
                                    <div class="custom-file d-flex">
                                        <label class="upload-image form-control" for="upload-1">
                                            <input type="file" id="upload-1" name="file">
                                        </label>
                                        <label class="upload-image upload-text" for="upload-2">
                                            <input type="file" id="upload-2" name="file">
                                            <img src="{{ $default_assets['review_image'] }}" alt="file"
                                                 class="img-fluid">
                                            {{ __('upload') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 instruction_body">
                            <label>{{ __('instructions') }}</label>
                            <div class="instruction"></div>
                        </div>
                        <div class="col-lg-12 text-center mt-3">
                            <button type="button" class="btn btn-primary form_submit"
                                    id="offline_submit">{{ __('proceed') }}</button>
                            <button class="btn btn-primary d-none loading" type="button" disabled>
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                <span class="sr-only"></span>
                            </button>
                        </div>
                    </div>
                </form>
            </div><!-- /.modal-body -->
        </div>
    </div>
</div>


<input type="hidden" class="total_amount" value="{{ $orders->sum('total_payable') }}">
<input type="hidden" class="trx_id" value="{{ $trx_id }}">
<input type="hidden" class="code" value="{{ $code }}">
<input type="hidden" class="url" value="{{ url('/') }}">
<input type="hidden" class="auth_user" value="{{ authUser() }}">
<input type="hidden" class="payment_success_url" value="{{ route('api.payment.success') }}">
<input type="hidden" id="stripe_key" value="{{ settingHelper('stripe_key') }}">

<script type="text/javascript" src="{{ static_asset('admin/js/jquery-3.3.1.min.js') }}"></script>
<script>
    window.url = '';
    window.base_url = $('.url').val();
    window.amount = $('.total_amount').val();
    window.trx_id = $('.trx_id').val();
    window.code = $('.code').val();
    window.user = $('.auth_user').val();
    window.token = '{{ $token }}';
</script>
<script type="text/javascript" src="{{ static_asset('admin/js/popper.min.js') }}"></script>
<script type="text/javascript" src="{{ static_asset('admin/js/bootstrap.min.js') }}"></script>
<script src="{{ static_asset('frontend/js/vue-toastr-2.js') }}"></script>


@if(settingHelper('is_paypal_activated') == 1)
    <script data-namespace="paypal_sdk"
            src="https://www.paypal.com/sdk/js?client-id={{ settingHelper('paypal_client_id') }}&currency=USD"></script>
    <script src="{{ static_asset('frontend/js/paypal.js') }}"></script>
@endif


<script type="text/javascript" src="{{ static_asset('admin/js/toastr.min.js') }}"></script>
<script>
    let ref = reference();
    (function ($) {
        'use strict';
        $(document).ready(function () {
            // Append script
            $(document).on('change', 'input[name="payment"]', function () {
                let val = $(this).val();

                $('.payment_btns').addClass('d-none');
                $('.div_btns').removeClass('d-none');

                let btn_selector = $('.' + val + '_btn');
                if (val == 'offline_method') {
                    let name = $(this).data('value');
                    let instructions = $(this).data('instructions');
                    let text = '{{ __('pay_with') }}' + ' ' + name;
                    btn_selector.text(text);
                    $('#offline .modal-title').text(text);
                    $('#offline .instruction').html(instructions);
                }
                if (val) {
                    btn_selector.removeClass('d-none');
                }
                if (val == 'cash_on_delivery' || val == "pay_later" || val == "wallet") {
                    $('.confirm_btn').removeClass('d-none');
                }
                if (val) {
                    $('.disable_btn').hide();
                } else {
                    $('.disable_btn').show();
                }

            });

            $(document).on('click', '#cod_n_pay_later_submit,#wallet_submit,#offline_submit', function (e) {
                e.preventDefault();
                let payment_type = $('input[name="payment"]:checked').val();
                let type = $(this).data('type');
                $('.payment_btns').addClass('d-none');

                if (type && type == 'wallet') {
                    payment_type = 'wallet'
                }

                let form = document.getElementById('offline_form');
                let formData = new FormData(form);

                if (window.user) {
                    let id = $('input[name="payment"]:checked').data('id');
                    formData.append('payment_type', payment_type);
                    formData.append('trx_id', window.trx_id);
                    formData.append('code', window.code);
                    formData.append('token', window.token);
                    if (payment_type == 'offline_method') {
                        formData.append('id', id);
                    }
                } else {
                    formData.append('payment_type', payment_type);
                    formData.append('trx_id', window.trx_id);
                    formData.append('code', window.code);
                    formData.append('guest', 1);
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ url('api/complete-order') }}',
                    processData: false,
                    contentType: false,
                    data: formData,
                    success: function (response) {
                        $('.form_submit').removeClass('d-none');
                        $('.loading').addClass('d-none');
                        if (response.error) {
                            toastr.error(response.error);
                        } else {
                            window.location.href = '{{ route('api.payment.success') }}';
                            toastr.success(response.success);
                        }
                    },
                    error: function (response) {
                        toastr.error(response.error);
                        $('.form_submit').removeClass('d-none');
                        $('.loading').addClass('d-none');
                    }
                })
            });


        });
    })(jQuery);

    function reference() {
        let text = "";
        let possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (let i = 0; i < 10; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        $('input[name = "tx_ref"]').val(text);

        return text;
    }

    let theButton = document.getElementById('iconHi');
    let theText = document.querySelector('.showHide');

    theButton.onclick = function () {
        theText.classList.toggle('ulHide');
        $("html, body").animate({scrollTop: 800}, 600);
    };

</script>
</body>
</html>