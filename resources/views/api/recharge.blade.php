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
    <link rel="stylesheet" href="{{ static_asset('frontend/css/main.css') }}">
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
            <div class="col-lg-8 col-md-5">
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
                          
                         
{{--                            @if(settingHelper('is_google_pay_activated'))--}}
{{--                                <li>--}}
{{--                                    <div class="input-checkbox">--}}
{{--                                        <input type="radio" id="google_pay" value="google_pay"--}}
{{--                                              name="payment">--}}
{{--                                        <label for="google_pay">--}}
{{--                                            <img src="{{ url('public/images/payment-method/google_pay.svg') }}"--}}
{{--                                                 alt="google_pay"--}}
{{--                                                 width="90"--}}
{{--                                                 class="img-fluid">--}}
{{--                                            {{ __('pay_with_google_pay') }}</label>--}}
{{--                                    </div>--}}
{{--                                </li>--}}
{{--                            @endif--}}
                        
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 pl-lg-5">
                <div class="order-summary paymentCustom">
                    <div class="peymentToggler">
                        <div class="paymentBTN-group">
                            <div class="order-total sm-fixed-bottom">
                                <div class="div_btns d-none">
                                    @php
                                    $params = "payment_mode=api&amount=$amount&token=$token&curr=$currency&type=wallet&lang=$lang";
                                    @endphp
                                    <a href="{{ url("stripe/redirect?$params")  }}" class="btn btn-primary paymentBTNFixed payment_btns d-none stripe_btn"> {{ __('pay_now') }}</a>

                                    <button class="btn btn-primary paymentBTNFixed d-none loading" type="button" disabled>
                                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        <span class="sr-only"></span>
                                    </button>

                                    <a href="javascript:void(0)" class="btn btn-primary paymentBTNFixed payment_btns d-none offline_method_btn" data-toggle="modal" data-target="#offline"> {{ __('pay_with') }} <span></span></a>
                                </div>

                                <div class="mx-auto w_40 payment_btns d-none paypal_btn" id="paypal-button-container"></div>

                                <form action="{{ url('user/recharge-wallet') }}" method="post">@csrf
                                    <input type="hidden" name="trx_id" value="{{ $trx_id }}">
                                    <input type="hidden" name="code" value="{{ $code }}">
                                    <input type="hidden" name="token" value="{{ $token }}">
                                    <input type="hidden" name="amount" value="{{ $amount }}">
                                    <input type="hidden" name="lang" value="{{ $lang }}">
                                    <input type="hidden" name="curr" value="{{ $currency }}">
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="sm-content-show"></div>
                </div>
            </div>
        </div><!-- /.row -->
    </div>
</section><!-- /.shopping-cart -->


{{--<input type="hidden" class="trx_id" value="{{ $trx_id }}">--}}
<input type="hidden" class="total_amount" value="{{ $amount }}">
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
    {{--window.token = '{{ $token }}';--}}
</script>
<script type="text/javascript" src="{{ static_asset('admin/js/popper.min.js') }}"></script>
<script type="text/javascript" src="{{ static_asset('admin/js/bootstrap.min.js') }}"></script>

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
                    $('.order-total').removeClass('pb-2');

                    let btn_selector = $('.' + val + '_btn');

                    if (val) {
                        btn_selector.removeClass('d-none');
                    }
                    if (val == 'cash_on_delivery' || val == "pay_later" || val == "wallet") {
                        $('.confirm_btn').removeClass('d-none');
                        $('.order-total').removeClass('pb-2');
                    } else if (val == 'offline_method') {
                        let method = $(this).data('value');
                        $('.offline_method_btn').removeClass('d-none');
                        $('.' + val + '_btn img').attr('src', method.image);
                        $('.' + val + '_btn span').text(method.name);
                        $('#offline .modal-title span').text(method.name);
                        if (method.instructions) {
                            $('.instruction_body').show();
                            $('.instruction_body .instruction').html(method.instructions);
                        } else {
                            $('.instruction_body').hide();
                        }

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
                        let method = $('input[name="payment"]:checked').data('value');
                        formData.append('payment_type', payment_type);
                        formData.append('trx_id', window.trx_id);
                        formData.append('code', window.code);
                        formData.append('token', window.token);
                        if (payment_type == 'offline_method') {
                            formData.append('id', method.id);
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

        // theButton.onclick = function () {
        //     theText.classList.toggle('ulHide');
        //     $("html, body").animate({ scrollTop: 800 }, 600);
        // };

    </script>
</body>
</html>