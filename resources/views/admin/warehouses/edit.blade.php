@extends('admin.partials.master')

@section('title')
    {{ __('Warehouse Update') }}
@endsection

@section('warehouses_active')
    active
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Update Warehouse') }}</h2>
                </div>
                <div class="buttons add-button">
                    <a href="{{ old('r') ? old('r') : (@$r ? $r : url()->previous() )}}"
                       class="btn btn-outline-primary"><i class='bx bx-arrow-back'></i>{{ __('Back') }}</a>
                </div>
            </div>
            <div class="row">
                <div class="col-6 middle">
                    <div class="card">
                        <div class="card-header input-title" id="Add">
                            <h4>{{ __('Update Warehouse') }}</h4>
                        </div>
                        <div class="card-body card-body-paddding">
                            <form class="" id="lang">
                                <div class="form-group">
                                    <label for="name">{{ __('Language') }}</label>
                                    <select class="form-control selectric lang" name="lang">
                                        <option value="">{{ __('Select Language') }}</option>
                                        @foreach($languages as $language)
                                            <option value="{{ $language->locale }}" {{($lang != '' ? ($language->locale == $lang ? 'selected' : '') : ($language->locale == 'en' ? 'selected' : '')) }}>{{ $language->name }}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('lang'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('lang') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </form>
                            <form method="POST" action="{{route('warehouse.update')}}">
                                @csrf
                                @method('put')
                                <div class="form-group">
                                    <label for="name">{{ __('Name') }} *</label>
                                    <input type="hidden" value="{{ $warehouse_lang->translation_null == 'not-found' ? '' : $warehouse_lang->id }}" name="warehouse_lang_id">
                                    <input type="hidden" value="{{$warehouse_lang->warehouse->id}}" name="warehouse_id">
                                    <input type="hidden" value="{{$lang}}" name="lang">
                                    <input type="hidden" value="{{$warehouse_lang->warehouse->code}}" name="code">
                                    <input type="text" name="name" id="name" value="{{ $warehouse_lang->name }}" class="form-control" required>
                                    @if ($errors->has('name'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('name') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="address">{{ __('Address') }} *</label>
                                    <textarea name="address" id="address" class="form-control" rows="8" required>{{$warehouse_lang->address}}</textarea>
                                    @if ($errors->has('address'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('address') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="phone">{{ __('Phone') }} *</label>
                                    <input type="phone" name="phone" id="phone" value="{{ $warehouse_lang->warehouse->phone }}" class="form-control" required>
                                    @if ($errors->has('phone'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('phone') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="number_of_shelves">{{ __('Number of Shelves') }} *</label>
                                    <input type="number" name="number_of_shelves" id="number_of_shelves" value="{{ $warehouse_lang->warehouse->number_of_shelves }}" class="form-control" required min="1">
                                    @if ($errors->has('number_of_shelves'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('number_of_shelves') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="columns_per_shelf">{{ __('Columns per Shelf') }} *</label>
                                    <input type="number" name="columns_per_shelf" id="columns_per_shelf" value="{{ $warehouse_lang->warehouse->columns_per_shelf }}" class="form-control" required min="1">
                                    @if ($errors->has('columns_per_shelf'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('columns_per_shelf') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="storage_capacity">{{ __('Storage Capacity') }} *</label>
                                    <input type="number" name="storage_capacity" id="storage_capacity" value="{{ $warehouse_lang->warehouse->storage_capacity }}" class="form-control" required min="1">
                                    @if ($errors->has('storage_capacity'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('storage_capacity') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="user_id">{{ __('Warehouse Manager') }} *</label>
                                    <select class="customer-by-ajax form-control selectric" value="" name="user_id" id="user_id" aria-hidden="true">
                                        <option value="" selected>{{ __('Select Manager') }}</option>
                                        @foreach($staffs as $staff)
                                            <option value="{{$staff->id}}" {{$warehouse_lang->warehouse->user_id == $staff->id ? 'selected' : ''}}>{{$staff->first_name}} {{$staff->last_name}}</option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('user_id'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('user_id') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-outline-primary" tabindex="4">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@include('admin.common.delete-ajax') 