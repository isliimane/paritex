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
                                            <select name="from_warehouse_id" id="from_warehouse_id" class="form-control select2" required>
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

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5>{{ __('Transfer Items') }}</h5>
                                        <div id="transfer-items">
                                            <div class="transfer-item card mb-3">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="required">{{ __('Product') }}</label>
                                                                <select name="items[0][product_id]" class="form-control select2 product-select" required>
                                                                    <option value="">{{ __('Select Product') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="required">{{ __('Stock') }}</label>
                                                                <select name="items[0][product_stock_id]" class="form-control select2 stock-select" required>
                                                                    <option value="">{{ __('Select Stock') }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label class="required">{{ __('Quantity') }}</label>
                                                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" required>
                                                                <small class="form-text text-muted quantity-hint"></small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1">
                                                            <div class="form-group">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-danger btn-block remove-item" style="display: none;">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary" id="add-item">
                                            <i class="fas fa-plus"></i> {{ __('Add Item') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label>{{ __('Notes') }}</label>
                                            <textarea name="notes" class="form-control" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> {{ __('Create Transfer') }}
                                        </button>
                                    </div>
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
            let itemCount = 1;
            const sourceWarehouseSelect = $('#from_warehouse_id');
            const destWarehouseSelect = $('#to_warehouse_id');
            const warehouseErrorDivId = 'warehouse-error';

            function validateWarehouseSelection() {
                const sourceVal = sourceWarehouseSelect.val();
                const destVal = destWarehouseSelect.val();
                $('#' + warehouseErrorDivId).remove();
                sourceWarehouseSelect.removeClass('is-invalid');
                destWarehouseSelect.removeClass('is-invalid');
                var isValid = true;

                if (sourceVal && destVal && sourceVal === destVal) {
                    const errorMessage = '{{ __("Source and Destination warehouses cannot be the same.") }}';
                    $('<div class="invalid-feedback d-block" id="' + warehouseErrorDivId + '"></div>')
                       .text(errorMessage)
                       .insertAfter($('#destination-capacity-hint'));
                    sourceWarehouseSelect.addClass('is-invalid');
                    destWarehouseSelect.addClass('is-invalid');
                    isValid = false;
                }
                return isValid;
            }

            function loadProducts(warehouseId, productSelect) {
                productSelect.empty().append('<option value="">{{ __("Select Product") }}</option>');
                const stockSelect = productSelect.closest('.transfer-item').find('.stock-select');
                stockSelect.empty().append('<option value="">{{ __("Select Stock") }}</option>');
                const quantityInput = productSelect.closest('.transfer-item').find('.quantity-input');
                quantityInput.val('').removeAttr('data-max-quantity');
                productSelect.closest('.transfer-item').find('.quantity-hint').text('');
                console.log(warehouseId);
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
            }

            function loadStocks(warehouseId, productId, stockSelect) {
                stockSelect.empty().append('<option value="">{{ __("Select Stock") }}</option>');
                const quantityInput = stockSelect.closest('.transfer-item').find('.quantity-input');
                quantityInput.val('').removeAttr('data-max-quantity');
                stockSelect.closest('.transfer-item').find('.quantity-hint').text('');

                if (warehouseId && productId) {
                    const url = '{{ route("warehouse.products.products.warehouse-stocks", ["warehouse" => "__WAREHOUSE_ID__", "product" => "__PRODUCT_ID__"]) }}'
                        .replace('__WAREHOUSE_ID__', warehouseId)
                        .replace('__PRODUCT_ID__', productId);
                    $.ajax({
                        url: url,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            $.each(data, function(key, stock) {
                                stockSelect.append('<option value="' + stock.id + '" data-quantity="' + stock.quantity + '">' + stock.name + ' (' + stock.quantity + ' available)</option>');
                            });
                        },
                        error: function(jqXHR, status, error) {
                            console.error("Failed to load stocks: ", status, error);
                            alert("{{ __('Failed to load stocks for the selected product.') }}");
                        }
                    });
                }
            }

            function validateQuantity(input) {
                const quantity = parseInt(input.val()) || 0;
                const maxQuantity = parseInt(input.attr('data-max-quantity')) || 0;
                const hintElement = input.closest('.form-group').find('.quantity-hint');
                let isValid = true;

                input.removeClass('is-invalid');
                hintElement.removeClass('text-danger').text('');

                if (input.val() !== '') {
                    if (quantity < 1) {
                        hintElement.text('{{ __("Quantity must be at least 1") }}').addClass('text-danger');
                        input.addClass('is-invalid');
                        isValid = false;
                    } else if (maxQuantity > 0 && quantity > maxQuantity) {
                        hintElement.text('{{ __("Quantity exceeds available stock") }}').addClass('text-danger');
                        input.addClass('is-invalid');
                        isValid = false;
                    }
                }

                return isValid;
            }

            // Load products when source warehouse changes
            sourceWarehouseSelect.on('change', function() {
                const warehouseId = $(this).val();
                $('.product-select').each(function() {
                    loadProducts(warehouseId, $(this));
                });
                validateWarehouseSelection();
            });

            // Load stocks when product changes
            $(document).on('change', '.product-select', function() {
                const warehouseId = sourceWarehouseSelect.val();
                const productId = $(this).val();
                const stockSelect = $(this).closest('.transfer-item').find('.stock-select');
                loadStocks(warehouseId, productId, stockSelect);
            });

            // Update quantity validation when stock changes
            $(document).on('change', '.stock-select', function() {
                const selectedOption = $(this).find('option:selected');
                const quantityInput = $(this).closest('.transfer-item').find('.quantity-input');
                const maxQuantity = selectedOption.data('quantity');
                
                quantityInput.attr('data-max-quantity', maxQuantity);
                validateQuantity(quantityInput);
            });

            // Validate quantity on input
            $(document).on('input', '.quantity-input', function() {
                validateQuantity($(this));
            });

            // Add new item
            $('#add-item').on('click', function() {
                const originalItem = $('.transfer-item').first();
                originalItem.find('select').select2('destroy');
                const newItem = originalItem.clone();
                originalItem.find('select').select2();
                
                newItem.find('select').val('').trigger('change');
                newItem.find('input').val('');
                newItem.find('.quantity-hint').text('');
                newItem.find('.remove-item').show();
                
                const newIndex = itemCount++;
                newItem.find('select, input').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
                    }
                });
                
                $('#transfer-items').append(newItem);
                newItem.find('select').select2();
                
                // Load products if source warehouse is selected
                const warehouseId = sourceWarehouseSelect.val();
                if (warehouseId) {
                    loadProducts(warehouseId, newItem.find('.product-select'));
                }
            });

            // Remove item
            $(document).on('click', '.remove-item', function() {
                if ($('.transfer-item').length > 1) {
                    $(this).closest('.transfer-item').remove();
                }
            });

            // Form submission - only add this if it doesn't exist
            if (!$('#transferForm').data('has-submit-handler')) {
                $('#transferForm').on('submit', function(e) {
                    e.preventDefault();
                    
                    let isValid = true;
                    let firstInvalidField = null;

                    // Validate warehouse selection
                    if (!validateWarehouseSelection()) {
                        isValid = false;
                        firstInvalidField = firstInvalidField || sourceWarehouseSelect;
                    }

                    // Validate each item
                    $('.transfer-item').each(function() {
                        const productSelect = $(this).find('.product-select');
                        const stockSelect = $(this).find('.stock-select');
                        const quantityInput = $(this).find('.quantity-input');

                        if (!productSelect.val()) {
                            isValid = false;
                            firstInvalidField = firstInvalidField || productSelect;
                        }

                        if (!stockSelect.val()) {
                            isValid = false;
                            firstInvalidField = firstInvalidField || stockSelect;
                        }

                        if (!validateQuantity(quantityInput)) {
                            isValid = false;
                            firstInvalidField = firstInvalidField || quantityInput;
                        }
                    });

                    if (!isValid) {
                        if (firstInvalidField) {
                            firstInvalidField.focus();
                        }
                        return false;
                    }

                    // If all validations pass, submit the form
                    this.submit();
                }).data('has-submit-handler', true);
            }
        });
    </script>
@endpush 