<?php $color = '#2c3e50'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>INV-{{ $order->code }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        td {
            vertical-align: top;
            padding: 5px;
        }
        
        .header-table td {
            padding-bottom: 15px;
        }
        
        .logo {
            max-height: 50px;
            max-width: 150px;
        }
        
        .invoice-title {
            font-size: 18pt;
            font-weight: bold;
            color: <?php echo $color; ?>;
            text-transform: uppercase;
        }
        
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            color: <?php echo $color; ?>;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        
        .items-table {
            margin: 20px 0;
        }
        
        .items-table thead td {
            background-color: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
            color: <?php echo $color; ?>;
            padding: 8px 5px;
        }
        
        .items-table tbody td {
            border-bottom: 1px solid #e0e0e0;
            padding: 8px 5px;
        }
        
        .summary-table {
            width: 350px;
            margin-left: auto;
            margin-right: 0;
        }
        
        .summary-table td {
            padding: 5px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-bold {
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 1px solid <?php echo $color; ?>;
            border-bottom: 1px solid <?php echo $color; ?>;
            font-weight: bold;
            color: <?php echo $color; ?>;
        }
        
        .footer-table {
            margin-top: 30px;
            border-top: 1px solid #e0e0e0;
            font-size: 9pt;
            color: #777777;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td width="30%">
                @php
                    $logo = settingHelper('invoice_logo');
                @endphp
                <img src="{{($logo != [] && @is_file_exists($logo['image_118x45'])) ? static_asset($logo['image_118x45']) : static_asset('images/default/dark-logo.png') }}"
                     alt="Logo" class="logo">
            </td>
            <td width="40%" align="center">
                <table>
                    <tr>
                        <td align="center" class="text-bold">{{ settingHelper('system_name') }}</td>
                    </tr>
                    <tr>
                        <td align="center">{{ settingHelper('contact_email') ? : settingHelper('header_contact_email') }}</td>
                    </tr>
                    <tr>
                        <td align="center">{{ settingHelper('contact_phone') ? : settingHelper('header_contact_phone') }}</td>
                    </tr>
                </table>
            </td>
            <td width="30%" align="right">
                <div class="invoice-title">{{ __('Invoice') }}</div>
                <div># {{ $order->code }}</div>
            </td>
        </tr>
    </table>
    
    <!-- Address Section -->
    <table>
        <tr>
            @if(!$order->pickupHub)
                <td width="33%">
                    <div class="section-title">{{ __('Shipping To') }}</div>
                    <table>
                        @php
                            $shipping_address = $order->shipping_address;
                        @endphp
                        @if($shipping_address)
                            <tr>
                                <td class="text-bold">{{ $shipping_address['name'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ $shipping_address['address'] }}, {{ @$shipping_address['city'] }}, {{ @$shipping_address['country'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ config('app.demo_mode') ? emailAddressMask($shipping_address['email']) : $shipping_address['email'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ @$shipping_address['phone_no'] }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
                <td width="33%">
                    <div class="section-title">{{ __('Bill To') }}</div>
                    <table>
                        @php
                            $billing_address = $order->billing_address;
                        @endphp
                        @if($billing_address)
                            <tr>
                                <td class="text-bold">{{ $billing_address['name'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ $billing_address['address'] }}, {{ @$billing_address['city'] }}, {{ @$billing_address['country'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ config('app.demo_mode') ? emailAddressMask($billing_address['email']) : $billing_address['email'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ @$billing_address['phone_no'] }}</td>
                            </tr>
                        @endif
                    </table>
                </td>
            @else
                <td width="60%">
                    <div class="section-title">{{ __('Pickup Hub') }}</div>
                    <table>
                        <tr>
                            <td width="20%" class="text-bold">{{ __('Name') }}:</td>
                            <td>{{ @$order->pickupHub->name }}</td>
                        </tr>
                        <tr>
                            <td width="20%" class="text-bold">{{ __('Manager') }}:</td>
                            <td>{{ @$order->pickupHub->incharge->full_name }}</td>
                        </tr>
                        <tr>
                            <td width="20%" class="text-bold">{{ __('Address') }}:</td>
                            <td>{{ @$order->pickupHub->address }}</td>
                        </tr>
                    </table>
                </td>
            @endif
            <td width="{{ !$order->pickupHub ? '34%' : '40%' }}">
                <div class="section-title">{{ __('Order Info') }}</div>
                <table>
                    <tr>
                        <td width="40%" class="text-bold">{{ __('Order No') }}:</td>
                        <td>{{ $order->code }}</td>
                    </tr>
                    <tr>
                        <td width="40%" class="text-bold">{{ __('Order Date') }}:</td>
                        <td>{{ date('M d, Y', strtotime($order->date)) }}</td>
                    </tr>
                    <tr>
                        <td width="40%" class="text-bold">{{ __('Payment Type') }}:</td>
                        <td>{{ ucwords(str_replace('_', ' ', $order->payment_type)) }}</td>
                    </tr>
                    <tr>
                        <td width="40%" class="text-bold">{{ __('Status') }}:</td>
                        <td>{{ $order->payment_status }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <td width="5%">#</td>
                <td width="45%">{{ __('Product') }}</td>
                <td width="15%" class="text-right">{{ __('Unit Price') }}</td>
                <td width="10%" class="text-center">{{ __('Quantity') }}</td>
                <td width="10%" class="text-right">{{ __('Tax') }}</td>
                <td width="15%" class="text-right">{{ __('Totals') }}</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderDetails as $key => $orderDetail)
                @php
                    $price = $orderDetail->retail_price > 0 ? $orderDetail->retail_price : $orderDetail->price;
                @endphp
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td>
                        <table>
                            <tr>
                                <td width="40">
                                    @if(!blank($orderDetail->product))
                                        <img src="{{ getFileLink('40x40', $orderDetail->product->thumbnail) }}"
                                             alt="{{ $orderDetail->product->name }}" width="40">
                                    @else
                                        <img src="{{ static_asset('images/default/default-image-40x40.png') }}"
                                             alt="N/A" width="40">
                                    @endif
                                </td>
                                <td>
                                    @if(!blank($orderDetail->product))
                                        {{ $orderDetail->product->getTranslation('name', \App::getLocale()) }}
                                        @if($orderDetail->variation != null)
                                            ({{ $orderDetail->variation }})
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td class="text-right">{{ get_price($price) }}</td>
                    <td class="text-center">{{ $orderDetail->quantity }}</td>
                    <td class="text-right">{{ $orderDetail->tax }}</td>
                    <td class="text-right">{{ get_price($price * $orderDetail->quantity) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <!-- Summary Table -->
    <table>
        <tr>
            <td width="65%"></td>
            <td width="35%">
                <table class="summary-table">
                    <tr>
                        <td width="60%" class="text-right">{{ __('Sub Total') }}:</td>
                        <td width="40%" class="text-right">{{ get_price($order->retail_sub_total > 0 ? $order->retail_sub_total : $order->sub_total, user_curr()) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(-) {{ __('Discount') }}:</td>
                        <td class="text-right">{{ get_price($order->retail_discount > 0 ? $order->retail_discount : $order->discount, user_curr()) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(-) {{ __('Coupon Discount') }}:</td>
                        <td class="text-right">{{ get_price($order->retail_coupon_discount > 0 ? $order->retail_coupon_discount : $order->coupon_discount, user_curr()) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(+) {{ __('Total Tax') }}:</td>
                        <td class="text-right">{{ get_price($order->retail_total_tax > 0 ? $order->retail_total_tax : $order->total_tax, user_curr()) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right text-bold">{{ __('Total Amount') }}:</td>
                        <td class="text-right text-bold">{{ get_price($order->retail_total_amount > 0 ? $order->retail_total_amount : $order->total_amount, user_curr()) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right">(+) {{ __('Shipping Cost') }}:</td>
                        <td class="text-right">{{ get_price($order->shipping_cost, user_curr()) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td class="text-right">{{ __('Net Payable') }}:</td>
                        <td class="text-right">{{ get_price($order->retail_total_payable > 0 ? $order->retail_total_payable : $order->total_payable, user_curr()) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    
    <!-- Footer -->
    <table class="footer-table">
        <tr>
            <td align="center">
                {{ __('Thank you for your business!') }}
            </td>
        </tr>
        <tr>
            <td align="center">
                {{ settingHelper('system_name') }} - {{ date('Y') }}
            </td>
        </tr>
    </table>
</body>
</html>