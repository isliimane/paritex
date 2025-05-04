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
                        <small id="quantityHelp" class="form-text text-muted"></small>
                    </div>
                    <input type="hidden" id="current_quantity" value="">
                    <input type="hidden" id="available_stock" value="">
                    <input type="hidden" id="available_space" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Update Product') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script')
<script>
    $(document).ready(function() {
        let editModal = $('#editProductModal');
        let quantityInput = $('#edit_quantity');
        let quantityHelp = $('#quantityHelp');
        let currentQuantity = $('#current_quantity');
        let availableStock = $('#available_stock');
        let availableSpace = $('#available_space');
        // Function to validate quantity
        function validateQuantity() {
            let quantity = parseInt(quantityInput.val()) || 0;
            let currentQty = parseInt(currentQuantity.val()) || 0;
            let stock = parseInt(availableStock.val()) || 0;
            let space = parseInt(availableSpace.val()) || 0;
            let isValid = true;

            // Reset validation state
            quantityInput.removeClass('is-invalid');
            quantityHelp.removeClass('text-danger').text('');

            if (quantity < 1) {
                quantityHelp.text('{{ __("Quantity must be at least 1") }}').addClass('text-danger');
                quantityInput.addClass('is-invalid');
                isValid = false;
            } else {
                // Calculate the difference in quantity
                let quantityDiff = quantity - currentQty;

                // Check if new quantity exceeds available stock
                if (quantityDiff > stock) {
                    quantityHelp.text('{{ __("Quantity exceeds available stock. Available:") }} ' + stock).addClass('text-danger');
                    quantityInput.addClass('is-invalid');
                    isValid = false;
                }

                // Check if new quantity exceeds available warehouse space
                if (quantityDiff > space) {
                    quantityHelp.text('{{ __("Quantity exceeds available warehouse space. Available:") }} ' + space).addClass('text-danger');
                    quantityInput.addClass('is-invalid');
                    isValid = false;
                }
            }

            return isValid;
        }

        // Validate on input change
        quantityInput.on('input', function() {
            validateQuantity();
        });

        // Validate before form submission
        $('#editProductForm').on('submit', function(e) {
            if (!validateQuantity()) {
                e.preventDefault();
            }
        });

        // When modal is shown, set up the data
        editModal.on('show.bs.modal', function(event) {
            let button = clickedButton;
            let quantity = button.data('quantity');
            let stock = button.data('available-stock');
            let space = button.data('available-space');

            currentQuantity.val(quantity);
            availableStock.val(stock);
            availableSpace.val(space);
            quantityInput.val(quantity);

            // Clear any previous validation messages
            quantityHelp.text('');
            quantityInput.removeClass('is-invalid');
        });
    });
</script>
@endpush 