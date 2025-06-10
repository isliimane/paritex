@extends('admin.partials.master')
@section('title', __('Edit Order'))
@section('main-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('Edit Order') }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('order.update', $order->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Billing Address Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5>{{ __('Billing Address') }}</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="billing_name">{{ __('Name') }}</label>
                                        <input type="text" class="form-control" id="billing_name" name="billing_address[name]" value="{{ $order->billing_address['name'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="billing_email">{{ __('Email') }}</label>
                                        <input type="email" class="form-control" id="billing_email" name="billing_address[email]" value="{{ $order->billing_address['email'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="billing_phone">{{ __('Phone') }}</label>
                                        <input type="text" class="form-control" id="billing_phone" name="billing_address[phone_no]" value="{{ $order->billing_address['phone_no'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="billing_postal_code">{{ __('Postal Code') }}</label>
                                        <input type="text" class="form-control" id="billing_postal_code" name="billing_address[postal_code]" value="{{ $order->billing_address['postal_code'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">

                                            <label for="billing-country-dropdown">{{ __('Country') }} *</label>
                                            <select class="form-control select2" name="billing_country_id" id="billing-country-dropdown">
                                                <option value="">{{ __('Select Country') }}</option>
                                                @foreach($countries as $key => $country)
                                                    <option value="{{ $country->id }}" {{ $country->id == $order->billing_address['address_ids']['country_id'] ? "selected" : "" }}>{{ $country->name }}</option>
                                                @endforeach
                                            </select>

                                            @if ($errors->has('country'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('country') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="billing-state-dropdown">{{ __('State') }}</label>
                                            <select class="form-control select2" name="billing_state_id" id="billing-state-dropdown">
                                                <option value="">{{ __('Select State') }}</option>
                                                @if($billing_country)
                                                    @foreach($billing_country->states as $key => $state)
                                                        <option value="{{ $state->id }}" {{ $state->id == $order->billing_address['address_ids']['state_id'] ? "selected" : "" }} >{{ $state->name }}
                                                    @endforeach
                                                @endif
                                            </select>

                                            @if ($errors->has('state'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('state') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="billing-city-dropdown">{{ __('City') }}</label>
                                            <select class="form-control select2" name="billing_city_id" id="billing-city-dropdown">
                                                <option value="">{{ __('Select State First') }}</option>
                                                @if($billing_state && $billing_state->cities)
                                                    @foreach($billing_state->cities as $key => $city)
                                                        <option value="{{ $city->id }}" {{ $city->id == $order->billing_address['address_ids']['city_id'] ? "selected" : "" }} >{{ $city->name }}
                                                    @endforeach
                                                @endif

                                            </select>

                                            @if ($errors->has('city'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('city') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="billing_address">{{ __('Address') }}</label>
                                        <input type="text" class="form-control" id="billing_address" name="billing_address[address]" value="{{ $order->billing_address['address'] ?? '' }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Shipping Address Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5>{{ __('Shipping Address') }}</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_name">{{ __('Name') }}</label>
                                        <input type="text" class="form-control" id="shipping_name" name="shipping_address[name]" value="{{ $order->shipping_address['name'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_email">{{ __('Email') }}</label>
                                        <input type="email" class="form-control" id="shipping_email" name="shipping_address[email]" value="{{ $order->shipping_address['email'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_phone">{{ __('Phone') }}</label>
                                        <input type="text" class="form-control" id="shipping_phone" name="shipping_address[phone_no]" value="{{ $order->shipping_address['phone_no'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_postal_code">{{ __('Postal Code') }}</label>
                                        <input type="text" class="form-control" id="shipping_postal_code" name="shipping_address[postal_code]" value="{{ $order->shipping_address['postal_code'] ?? '' }}">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="shipping-country-dropdown">{{ __('Country') }} *</label>
                                            <select class="form-control select2" name="shipping_country_id" id="shipping-country-dropdown">
                                                <option value="">{{ __('Select Country') }}</option>
                                                @foreach($countries as $key => $country)
                                                    <option value="{{ $country->id }}" {{ $country->id == $order->shipping_address['address_ids']['country_id'] ? "selected" : "" }}>{{ $country->name }}</option>
                                                @endforeach
                                            </select>

                                            @if ($errors->has('country'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('country') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="shipping-state-dropdown">{{ __('State') }}</label>
                                            <select class="form-control select2" name="shipping_state_id" id="shipping-state-dropdown">
                                                <option value="">{{ __('Select State') }}</option>
                                                @if($shipping_country)
                                                    @foreach($shipping_country->states as $key => $state)
                                                        <option value="{{ $state->id }}" {{ $state->id == $order->shipping_address['address_ids']['state_id'] ? "selected" : "" }} >{{ $state->name }}
                                                    @endforeach
                                                @endif
                                            </select>

                                            @if ($errors->has('state'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('state') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="shipping-city-dropdown">{{ __('City') }}</label>
                                            <select class="form-control select2"
                                             name="shipping_city_id" id="shipping-city-dropdown">
                                                <option value="">{{ __('Select State First') }}</option>
                                                @if($shipping_state && $shipping_state->cities)
                                                    @foreach($shipping_state->cities as $key => $city)
                                                        <option value="{{ $city->id }}"
                                                        data-cost="{{ $city->shipping_cost }}" {{ $city->id == $order->shipping_address['address_ids']['city_id'] ? "selected" : "" }} >{{ $city->name }}
                                                    @endforeach
                                                @endif

                                            </select>

                                            @if ($errors->has('city'))
                                                <div class="invalid-feedback">
                                                    <p>{{ $errors->first('city') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="shipping_address">{{ __('Address') }}</label>
                                        <input type="text" class="form-control" id="shipping_address" name="shipping_address[address]" value="{{ $order->shipping_address['address'] ?? '' }}">
                                    </div>
                                </div>
                                
                            </div>

                            <!-- Order Details Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_cost">{{ __('Shipping Cost') }}</label>
                                        <input type="number" class="form-control" id="shipping_cost" name="shipping_cost" value="{{ $order->shipping_cost }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Order Items Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5>{{ __('Order Items') }}</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>{{ __('Product') }}</th>
                                                    <th>{{ __('Price') }}</th>
                                                    <th>{{ __('Quantity') }}</th>
                                                    <th>{{ __('Tax') }}</th>
                                                    <th>{{ __('Discount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($order->orderDetails as $detail)
                                                    <tr>
                                                        <input type="hidden" name="order_details[{{ $detail->id }}][id]" value="{{ $detail->id }}">
                                                        <td>
                                                        
                                                            @if($detail->product)
                                                                <div class="ml-1">
                                                                    {{ $detail->product->getTranslation('name', \App::getLocale()) }}
                                                                    @if($detail->variation != null)
                                                                        ({{ $detail->variation }})
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <div class="ml-1">
                                                                    N/A
                                                                </div>
                                                            @endif
                                                    
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="order_details[{{ $detail->id }}][price]" value="{{ $detail->price }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="order_details[{{ $detail->id }}][quantity]" value="{{ $detail->quantity }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="order_details[{{ $detail->id }}][tax]" value="{{ $detail->tax }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="order_details[{{ $detail->id }}][discount]" value="{{ $detail->discount }}">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">{{ __('Update Order') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 
@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/ajax-country-city-state.js') }}"></script>
    <script src="{{ static_asset('admin/js/countries.js') }}"></script>
    <script>
        $(document).ready(function(){
            $('#shipping-city-dropdown').on('change', function(){
                console.log($(this).find('option:selected').data('cost'));
                var shipping_cost = $(this).find('option:selected').data('cost');
                $('#shipping_cost').val(shipping_cost);
            });
        });
    </script>
@endpush