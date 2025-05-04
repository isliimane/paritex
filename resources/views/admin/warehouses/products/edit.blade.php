@extends('admin.partials.master')
@section('title', __('Edit Warehouse Product'))
@section('main-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('Edit Product in') }} {{ $warehouse->name }}</h4>
                    </div>
                    <div class="card-body">
                        @if(hasPermission('warehouse_product_edit'))
                            <form action="{{ route('warehouse.products.update', [$warehouse->id, $warehouseProduct->product_id]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="form-group">
                                    <label>{{ __('Product') }}</label>
                                    <input type="text" class="form-control" value="{{ $warehouseProduct->product->name }}" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="quantity">{{ __('Quantity') }} *</label>
                                    <input type="number" name="quantity" id="quantity" class="form-control" required min="0" value="{{ $warehouseProduct->quantity }}">
                                    @error('quantity')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">{{ __('Update Product') }}</button>
                                    <a href="{{ route('warehouse.products.index', $warehouse->id) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                                </div>
                            </form>
                        @else
                            <div class="alert alert-danger">
                                {{ __('You do not have permission to edit products in this warehouse.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 