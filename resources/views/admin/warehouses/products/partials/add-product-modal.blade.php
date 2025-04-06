<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">{{ __('Add Product to Warehouse') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addProductForm" action="{{ route('warehouse.products.store', $warehouse->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product_id">{{ __('Product') }} *</label>
                        <select name="product_id" id="product_id" class="form-control" required>
                            <option value="">{{ __('Select Product') }}</option>
                            @foreach($allProducts as $product)
                                <option value="{{ $product->id }}" data-stock="{{ $product->stock->sum('available_quantity') }}">
                                    {{ $product->getTranslation('name', \App::getLocale()) }} ({{ __('Available:') }} {{ $product->stock->sum('available_quantity') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="product_stock_id">{{ __('Stock') }} *</label>
                        <select name="product_stock_id" id="product_stock_id" class="form-control" required>
                            <option value="">{{ __('Select Stock') }}</option>
                        </select>
                        <small class="form-text text-muted" id="stockInfo"></small>
                    </div>

                    <div class="form-group">
                        <label for="quantity">{{ __('Quantity') }} *</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" required min="1">
                        <small class="form-text text-muted">
                            {{ __('Warehouse Capacity:') }} {{ $warehouse->storage_capacity }} |
                            {{ __('Current Usage:') }} <span id="currentUsage">{{ $products->sum('quantity') }}</span> |
                            {{ __('Available Space:') }} <span id="availableSpace">{{ $warehouse->storage_capacity - $products->sum('quantity') }}</span>
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="shelf_number">{{ __('Shelf Number') }} *</label>
                        <input type="number" name="shelf_number" id="shelf_number" class="form-control" required min="1" max="{{ $warehouse->number_of_shelves }}">
                        <small class="form-text text-muted">{{ __('Maximum:') }} {{ $warehouse->number_of_shelves }}</small>
                    </div>

                    <div class="form-group">
                        <label for="column_number">{{ __('Column Number') }} *</label>
                        <input type="number" name="column_number" id="column_number" class="form-control" required min="1" max="{{ $warehouse->columns_per_shelf }}">
                        <small class="form-text text-muted">{{ __('Maximum:') }} {{ $warehouse->columns_per_shelf }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Add Product') }}</button>
                </div>
            </form>
        </div>
    </div>
</div> 