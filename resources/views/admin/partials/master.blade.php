<!DOCTYPE html>
<html lang="en">
@include('admin.partials.header-assets')

    <body>
        <div id="app">
            <div class="main-wrapper">
                @include('admin.partials.header')
                @include('admin.partials.sidebar')
                <div class="main-content">
                <!-- Main Content -->
                @yield('main-content')
                <!-- Main Content End -->
                </div>
                @include('admin.partials.footer')
            </div>
        </div>
        @include('admin.partials.footer-assets')
        @include('admin.partials.message')
        <input type="hidden" value="{{ settingHelper('live_api_currency') }}" id="is_currency_api_enabled">
        <input type="hidden" value="{{route('home')}}" id="url">
        <input type="hidden" value="{{route('index')}}" id="assets">
        <input name="get-me" type="hidden" id="get_user_type" value="admin" />
        <input type="hidden" class="sure" value="{{ __('Are you sure? ') }}">
        <input type="hidden" class="confirm_btn" value="{{ __('yes_do_it') }}">
        <input type="hidden" class="product_alert_danger" value="{{ __('product_disabled') }}">
        <input type="hidden" class="product_alert_success" value="{{ __('product_enabled') }}">
        @yield('modal')
        <div class="overlayText d-none">
            <div>
                <img src="{{ static_asset('images/default/preloader.gif') }}" alt="updater">
                <p>{{ __('update_text') }}</p>
                <p>{{ __('update_browser_text') }}</p>
            </div>
        </div>
    </body>
</html>
