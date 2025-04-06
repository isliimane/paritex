<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">{{ __('Edit Product in Warehouse') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_quantity">{{ __('Quantity') }} *</label>
                        <input type="number" name="quantity" id="edit_quantity" class="form-control" required min="1">
                    </div>
                    <div class="form-group">
                        <label for="edit_shelf_number">{{ __('Shelf Number') }} *</label>
                        <input type="number" name="shelf_number" id="edit_shelf_number" class="form-control" required min="1" max="{{ $warehouse->number_of_shelves }}">
                        <small class="form-text text-muted">{{ __('Maximum:') }} {{ $warehouse->number_of_shelves }}</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_column_number">{{ __('Column Number') }} *</label>
                        <input type="number" name="column_number" id="edit_column_number" class="form-control" required min="1" max="{{ $warehouse->columns_per_shelf }}">
                        <small class="form-text text-muted">{{ __('Maximum:') }} {{ $warehouse->columns_per_shelf }}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update Product') }}</button>
                </div>
            </form>
        </div>
    </div>
</div> 