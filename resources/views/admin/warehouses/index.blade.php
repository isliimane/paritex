@extends('admin.partials.master')

@section('title')
    {{ __('Warehouses List') }}
@endsection

@section('warehouses_active')
    active
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Warehouses') }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $warehouses->total() . ' ' . __('Warehouses') }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('All Warehouses') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" id="sorting">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" placeholder="{{ __('Search') }}" value="{{ @$search }}">
                                        <div class="input-group-btn">
                                            <button class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <tbody>
                                    <tr>
                                        <th>{{ __('Code') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Manager') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Options') }}</th>
                                    </tr>
                                    @foreach($warehouses as $warehouse)
                                        <tr id="row_{{ $warehouse->id }}">
                                            <td>{{ $warehouse->code }}</td>
                                            <td>{{ $warehouse->name }}</td>
                                            <td>{{ $warehouse->phone }}</td>
                                            <td>{{ $warehouse->incharge->first_name }} {{ $warehouse->incharge->last_name }}</td>
                                            <td>
                                                <label class="custom-switch mt-2">
                                                    <input type="checkbox" name="custom-switch-checkbox" value="warehouse-status-change/{{ $warehouse->id }}" {{ $warehouse->status == 1 ? 'checked' : '' }} class="{{ $warehouse->status == 1 ? 'status-change' : 'status-change' }} custom-switch-input">
                                                    <span class="custom-switch-indicator"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <a href="{{ route('warehouse.products.index', $warehouse->id) }}" class="btn btn-outline-info btn-circle" data-toggle="tooltip" title="" data-original-title="{{ __('Manage Products') }}">
                                                    <i class="bx bx-package"></i>
                                                </a>
                                                <a href="{{ route('warehouse.edit', $warehouse->id) }}" class="btn btn-outline-secondary btn-circle" data-toggle="tooltip" title="" data-original-title="{{ __('Edit') }}">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <a href="javascript:void(0)" onclick="delete_row('delete/warehouses/',{{$warehouse->id}})" class="btn btn-outline-danger btn-circle" data-toggle="tooltip" title="" data-original-title="{{ __('Delete') }}">
                                                    <i class="bx bx-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            {{ $warehouses->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
            @if(hasPermission('warehouse_create'))
                <div class="row">
                    <div class="col-6 middle">
                        <div class="card">
                            <div class="card-header input-title">
                                <h4>{{ __('Add New Warehouse') }}</h4>
                            </div>
                            <div class="card-body card-body-paddding">
                                <form method="POST" action="{{ route('warehouse.store') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label for="code">{{ __('Code') }} *</label>
                                        <input type="text" name="code" id="code" value="{{ old('code') }}" class="form-control" required>
                                        @if ($errors->has('code'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('code') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="name">{{ __('Name') }} *</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
                                        @if ($errors->has('name'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('name') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="address">{{ __('Address') }} *</label>
                                        <textarea name="address" id="address" class="form-control" rows="8" required>{{ old('address') }}</textarea>
                                        @if ($errors->has('address'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('address') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">{{ __('Phone') }} *</label>
                                        <input type="phone" name="phone" id="phone" value="{{ old('phone') }}" class="form-control" required>
                                        @if ($errors->has('phone'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('phone') }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="storage_capacity">{{ __('Storage Capacity') }} *</label>
                                        <input type="number" name="storage_capacity" id="storage_capacity" value="{{ old('storage_capacity') }}" class="form-control" required min="1">
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
                                                <option value="{{ $staff->id }}" {{ old('user_id') == $staff->id ? 'selected' : '' }}>{{ $staff->first_name }} {{ $staff->last_name }}</option>
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
                                            {{ __('Save') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@include('admin.common.delete-ajax') 