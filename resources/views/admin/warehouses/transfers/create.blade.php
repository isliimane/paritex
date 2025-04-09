@extends('admin.partials.master')

@section('title', __('Create Warehouse Transfer'))

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Create Warehouse Transfer') }}</h2>
                    <p class="section-lead">
                        {{ __('Transfer products between warehouses') }}
                    </p>
                </div>
                <div class="buttons mt-3">
                    <a href="{{ route('transfers.index') }}" class="btn btn-outline-primary mr-2">
                        <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-exchange-alt"></i> {{ __('Transfer Details') }}</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('transfers.store') }}" method="POST" id="transferForm">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="from_warehouse_id" class="required d-block">
                                                <i class="fas fa-warehouse"></i> {{ __('Source Warehouse') }}
                                            </label>
                                            <select name="from_warehouse_id" id="from_warehouse_id" class="form-control select2 " required>
                                                <option value="">{{ __('Select Source Warehouse') }}</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}" data-capacity="{{ $warehouse->storage_capacity }}">
                                                        {{ $warehouse->getTranslation('name', \App::getLocale()) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted" id="source-capacity-hint"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="to_warehouse_id" class="required d-block">
                                                <i class="fas fa-warehouse"></i> {{ __('Destination Warehouse') }}
                                            </label>
                                            <select name="to_warehouse_id" id="to_warehouse_id" class="form-control select2" required>
                                                <option value="">{{ __('Select Destination Warehouse') }}</option>
                                                @foreach($warehouses as $warehouse)
                                                    <option value="{{ $warehouse->id }}" data-capacity="{{ $warehouse->storage_capacity }}">
                                                        {{ $warehouse->getTranslation('name', \App::getLocale()) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted" id="destination-capacity-hint"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="product_id" class="required d-block">
                                                <i class="fas fa-box"></i> {{ __('Product') }}
                                            </label>
                                            <select name="product_id" id="product_id" class="form-control select2" required>
                                                <option value="">{{ __('Select Product') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="product_stock_id" class="required d-block">
                                                <i class="fas fa-boxes"></i> {{ __('Stock') }}
                                            </label>
                                            <select name="product_stock_id" id="product_stock_id" class="form-control select2" required>
                                                <option value="">{{ __('Select Stock') }}</option>
                                            </select>
                                            <small class="form-text text-muted" id="available-quantity-hint"></small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity" class="required d-block">
                                                <i class="fas fa-balance-scale"></i> {{ __('Quantity') }}
                                            </label>
                                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                                            <small class="form-text text-muted" id="quantity-hint"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="notes" class="d-block">
                                                <i class="fas fa-sticky-note"></i> {{ __('Notes') }}
                                            </label>
                                            <textarea name="notes" id="notes" class="form-control" rows="1"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> {{ __('Create Transfer') }}
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

@push('style')
    <style>
        .required:after {
            content: " *";
            color: red;
        }
        .select2.select2-container.select2-container--default{
            width: 100% !important;
        }        
        .form-text {
            margin-top: 0.25rem;
        }
        
    </style>
@endpush

@push('script')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                width: '100%'
            });

            // Helper function to format numbers
            function formatNumber(number) {
                return new Intl.NumberFormat().format(number);
            }

            // Function to validate warehouse selection
            const sourceWarehouseSelect = $('#from_warehouse_id');
            const destWarehouseSelect = $('#to_warehouse_id');
            const warehouseErrorDivId = 'warehouse-selection-error';

            function validateWarehouseSelection() {
                const sourceVal = sourceWarehouseSelect.val();
                const destVal = destWarehouseSelect.val();
                $('#' + warehouseErrorDivId).remove(); // Remove previous error
                sourceWarehouseSelect.removeClass('is-invalid');
                destWarehouseSelect.removeClass('is-invalid');
                var isValid = true;

                if (sourceVal && destVal && sourceVal === destVal) {
                    const errorMessage = '{{ __("Source and Destination warehouses cannot be the same.") }}';
                    // Append error message after the hint element
                    $('<div class="invalid-feedback d-block" id="' + warehouseErrorDivId + '"></div>')
                       .text(errorMessage)
                       .insertAfter($('#destination-capacity-hint'));
                    sourceWarehouseSelect.addClass('is-invalid');
                    destWarehouseSelect.addClass('is-invalid');
                    isValid = false; // Indicate invalid selection
                }
                return isValid; // Indicate validity status
            }

            // Load products when source warehouse changes
            sourceWarehouseSelect.on('change', function() {
                var warehouseId = $(this).val();
                var productSelect = $('#product_id');
                var stockSelect = $('#product_stock_id');

                productSelect.empty().append('<option value="">{{ __("Select Product") }}</option>').trigger('change');
                stockSelect.empty().append('<option value="">{{ __("Select Stock") }}</option>').trigger('change');
                $('#available-quantity-hint').text('');
                $('#quantity').val(''); // Clear quantity
                $('#quantity').removeAttr('data-max-quantity');
                validateQuantity(); // Clear validation
                validateWarehouseSelection(); // Check if same as dest

                if (warehouseId) {
                    const url = '{{ route("warehouse.products.get", ["warehouse_id" => "__WAREHOUSE_ID__"]) }}'.replace('__WAREHOUSE_ID__', warehouseId);
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $.each(data, function(key, product) {
                                productSelect.append('<option value="' + product.id + '">' + product.product_name + '</option>');
                            });
                        },
                        error: function(jqXHR, status, error) {
                           console.error("Failed to load products: ", status, error);
                           alert("{{ __('Failed to load products for the selected warehouse.') }}");
                        }
                    });
                }
            });

            // Display destination warehouse capacity and available space & check same warehouse
            destWarehouseSelect.on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var capacity = selectedOption.data('capacity');
                var warehouseId = $(this).val();
                var sourceWarehouseId = sourceWarehouseSelect.val();
                var hintElement = $('#destination-capacity-hint');
                var quantityInput = $('#quantity');

                // Clear previous destination validation attributes/state
                hintElement.text('');
                quantityInput.removeAttr('data-available-space');
                validateQuantity(); // Re-validate quantity as destination constraint removed

                // Validate warehouse selection
                validateWarehouseSelection();

                if (warehouseId && capacity !== undefined) {
                    hintElement.text('{{ __("Capacity:") }} ' + formatNumber(capacity) + ' | {{ __("Checking available space...") }}');
                    const url = '{{ route("warehouse.current.quantity", ["warehouse" => "__WAREHOUSE_ID__"]) }}'.replace('__WAREHOUSE_ID__', warehouseId);

                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        success: function(response) {
                            if (response.current_quantity !== undefined) {
                                var currentQuantity = response.current_quantity;
                                var availableSpace = capacity - currentQuantity;
                                availableSpace = Math.max(0, availableSpace);
                                hintElement.text(
                                    '{{ __("Warehouse Capacity:") }} ' + formatNumber(capacity) +
                                    ' | {{ __("Current Usage:") }} ' + formatNumber(currentQuantity) +
                                    ' | {{ __("Available Space:") }} ' + formatNumber(availableSpace)
                                );
                                quantityInput.attr('data-available-space', availableSpace);
                            } else {
                                hintElement.text(
                                    '{{ __("Capacity:") }} ' + formatNumber(capacity) +
                                    ' | {{ __("Could not fetch available space") }}'
                                );
                                console.error("Error fetching current quantity: ", response.error || 'Unknown error');
                            }
                            // Always re-validate quantity after getting destination info (or failing)
                            validateQuantity();
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            hintElement.text(
                                '{{ __("Capacity:") }} ' + formatNumber(capacity) +
                                ' | {{ __("Could not fetch available space") }}'
                            );
                            console.error("AJAX error fetching current quantity: ", textStatus, errorThrown);
                            // Always re-validate quantity after error
                            validateQuantity();
                        }
                    });
                }
            }).trigger('change'); // Trigger on page load for destination

            // Load stocks when product changes
            $('#product_id').on('change', function() {
                var productId = $(this).val();
                var warehouseId = sourceWarehouseSelect.val();
                var stockSelect = $('#product_stock_id');

                stockSelect.empty().append('<option value="">{{ __("Select Stock") }}</option>').trigger('change');
                $('#available-quantity-hint').text('');
                $('#quantity').val(''); // Clear quantity
                $('#quantity').removeAttr('data-max-quantity');
                validateQuantity();

                if (productId && warehouseId) {
                    const url = "{{ route('warehouse.products.products.warehouse-stocks', ['warehouse' => ':warehouseId', 'product' => ':productId']) }}".replace(':warehouseId', warehouseId)
                    .replace(':productId', productId);
                    console.log(url);
                    
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $.each(data, function(key, stock) {
                                stockSelect.append('<option value="' + stock.id + '" data-quantity="' + stock.quantity + '">' + stock.name + ' ({{ __('Sku:') }} ' + stock.sku + ')</option>');
                            });
                        },
                         error: function(jqXHR, status, error) {
                           console.error("Failed to load stocks: ", status, error);
                           alert("{{ __('Failed to load stocks for the selected product and warehouse.') }}");
                        }
                    });
                }
            });

            // Show available quantity for selected stock and set max quantity (Source)
            $('#product_stock_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var availableQuantity = selectedOption.data('quantity');
                var quantityInput = $('#quantity');
                var hintElement = $('#available-quantity-hint');

                if (availableQuantity !== undefined) {
                    hintElement.text('{{ __("Available Quantity:") }} ' + formatNumber(availableQuantity));
                    quantityInput.attr('data-max-quantity', availableQuantity);
                } else {
                    hintElement.text('');
                    quantityInput.removeAttr('data-max-quantity');
                }
                validateQuantity();
            });

            // Validate quantity input
            function validateQuantity() {
                var quantityInput = $('#quantity');
                var quantity = parseInt(quantityInput.val()) || 0;
                var maxQuantity = parseInt(quantityInput.attr('data-max-quantity')) || 0;
                var availableSpace = parseInt(quantityInput.attr('data-available-space')) || 0;
                var hintElement = $('#quantity-hint');
                var isValid = true;

                // Clear previous validation state
                quantityInput.removeClass('is-invalid');
                hintElement.removeClass('text-danger').text('');

                // Only validate if there's input or during form submission
                if (quantityInput.val() !== '' || $('#transferForm').data('validating')) {
                    // Validate minimum quantity
                    if (quantity < 1) {
                        hintElement.text('{{ __("Quantity must be at least 1") }}').addClass('text-danger');
                        quantityInput.addClass('is-invalid');
                        isValid = false;
                    }
                    // Validate against source stock
                    else if (maxQuantity > 0 && quantity > maxQuantity) {
                        hintElement.text('{{ __("Quantity exceeds available stock") }}').addClass('text-danger');
                        quantityInput.addClass('is-invalid');
                        isValid = false;
                    }
                    // Validate against destination space
                    else if (availableSpace > 0 && quantity > availableSpace) {
                        hintElement.text('{{ __("Quantity exceeds available space in destination warehouse") }}').addClass('text-danger');
                        quantityInput.addClass('is-invalid');
                        isValid = false;
                    }
                }

                return isValid;
            }

            // Validate quantity on input
            $('#quantity').on('input', validateQuantity);

            // Intercept form submission
            $('#transferForm').on('submit', function(e) {
                e.preventDefault();
                
                // Set validating flag
                $(this).data('validating', true);
                
                // Validate all required fields
                var isValid = true;
                var firstInvalidField = null;

                // Check warehouse selection
                if (!validateWarehouseSelection()) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || sourceWarehouseSelect;
                }

                // Check product selection
                if (!$('#product_id').val()) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || $('#product_id');
                }

                // Check stock selection
                if (!$('#product_stock_id').val()) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || $('#product_stock_id');
                }

                // Validate quantity
                if (!validateQuantity()) {
                    isValid = false;
                    firstInvalidField = firstInvalidField || $('#quantity');
                }

                // Clear validating flag
                $(this).data('validating', false);

                if (!isValid) {
                    // Focus the first invalid field
                    if (firstInvalidField) {
                        firstInvalidField.focus();
                    }
                    return false;
                }

                // If all validations pass, submit the form
                this.submit();
            });
        });
    </script>
@endpush 