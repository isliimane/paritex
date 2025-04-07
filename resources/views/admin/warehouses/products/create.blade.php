@extends('admin.partials.master')
@section('title', __('Add Product to Warehouse'))
@section('main-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">{{ __('Add Product to') }} {{ $warehouse->name }}</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('warehouse.products.store', $warehouse->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="product_id">{{ __('Product') }} *</label>
                                <select name="product_id" id="product_id" class="form-control" required>
                                    <option value="">{{ __('Select Product') }}</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="quantity">{{ __('Quantity') }} *</label>
                                <input type="number" name="quantity" id="quantity" class="form-control" required min="0">
                                @error('quantity')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">{{ __('Add Product') }}</button>
                                <a href="{{ route('warehouse.products.index', $warehouse->id) }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 