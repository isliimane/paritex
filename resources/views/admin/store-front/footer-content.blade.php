@extends('admin.partials.master')
@section('store_front_active')
    active
@endsection
@section('page-style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/summernote-bs4.css') }}">
@endsection
@section('payment_method_banner')
    active
@endsection
@section('title')
    {{ __('Footer-Content') }}
@endsection
@section('main-content')
<section class="section">
    <div class="section-body">
        <div class="row">
            @include('admin.store-front.footer-content-sidebar')
            <div class="col-12 col-sm-12 col-md-8 col-lg-9">
                <div class="tab-content no-padding" id="myTab2Content">
                    <div class="tab-pane fade show active" id="about" role="tabpane1"aria-labelledby="about-tab">
                        <div class="card">
                            <div class="card-header">
                                {{ __('About Widget') }}
                            </div>
                            <div class="card-body col-md-10 middle">
                                <form>
                                    <div class="form-group">
                                        <label for="">{{ __('Footer Logo') }}</label>
                                        <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="customFile" >
                                        <label class="custom-file-label" for="customFile" >{{ __('Choose file') }}</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('About description') }}</label>
                                        <div class="form-group row mb-12">
                                            <div class="col-sm-12 col-md-12">
                                            <textarea class="summernote"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="">{{ __('Play Store Link') }}</label>
                                        <input type="text" class="form-control" id="" placeholder="https://">
                                    </div>
                                    <div class="text-md-right">
                                    <button class="btn btn-outline-primary" id="save-btn">Save</button>
                                    </div>
                                </form>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

