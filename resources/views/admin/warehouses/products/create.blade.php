@extends('admin.partials.master')
@section('title')
    {{ __('Add Products to Warehouse') }}
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Add Products to') }}: {{ $warehouse->name }}</h2>
                    <p class="section-lead">
                        {{ __('Add multiple products to the warehouse') }}
                    </p>
                </div>
                <div class="buttons mt-3">
                    <a href="{{ route('warehouse.products.index', $warehouse->id) }}" class="btn btn-outline-primary">
                        <i class="bx bx-arrow-back"></i> {{ __('Back') }}
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('Add Products') }}</h4>
                        </div>
                        <div class="card-body">
                            <form id="addProductsForm" action="{{ route('warehouse.products.store', $warehouse->id) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-12">
                                        <div id="product-items">
                                            <div class="product-item card mb-3">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <div class="form-group">
                                                                <label class="required">{{ __('Product') }}</label>
                                                                <select name="items[0][product_id]" class="form-control select2 product-select" required>
                                                                    <option value="">{{ __('Select Product') }}</option>
                                                                    @foreach($allProducts as $product)
                                                                        <option value="{{ $product->id }}">{{ $product->getTranslation('name', \App::getLocale()) }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label class="required">{{ __('Stock') }}</label>
                                                                <select name="items[0][product_stock_id]" class="form-control select2 stock-select" required>
                                                                    <option value="">{{ __('Select Stock') }}</option>
                                                                </select>
                                                                <small class="form-text text-muted stock-hint"></small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="form-group">
                                                                <label class="required">{{ __('Quantity') }}</label>
                                                                <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" required>
                                                                <small class="form-text text-muted quantity-hint"></small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-1 d-flex align-items-center justify-content-center">
                                                            <div class="form-group">
                                                                <label>&nbsp;</label>
                                                                <button type="button" class="btn btn-outline-danger remove-item px-3" style="display: none;">
                                                                    <i class="bx bx-trash"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary" id="add-item">
                                            <i class="bx bx-plus"></i> {{ __('Add Product') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save"></i> {{ __('Save') }}
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
        const warehouseId = {{ $warehouse->id }};
        const availableSpace = {{ $availableSpace }};

        function loadStocks(productId, stockSelect) {
            stockSelect.empty().append('<option value="">{{ __("Select Stock") }}</option>');
            const quantityInput = stockSelect.closest('.product-item').find('.quantity-input');
            quantityInput.val('').removeAttr('data-max-quantity');
            stockSelect.closest('.product-item').find('.stock-hint').text('');

            if (productId) {
                const url = '{{ route("warehouse.products.products.stocks", ["warehouse" => $warehouse->id, "product" => ":productId"]) }}'.replace(':productId', productId);
                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $.each(data, function(key, stock) {
                            stockSelect.append(
                                `<option value="${stock.id}" data-quantity="${stock.available_quantity}">
                                    ${stock.sku} ({{ __('Available:') }} ${stock.available_quantity})
                                </option>`
                            );
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
                } else if (quantity > availableSpace) {
                    hintElement.text('{{ __("Quantity exceeds available warehouse space") }}').addClass('text-danger');
                    input.addClass('is-invalid');
                    isValid = false;
                }
            }

            return isValid;
        }

        // Load stocks when product changes
        $(document).on('change', '.product-select', function() {
            const productId = $(this).val();
            const stockSelect = $(this).closest('.product-item').find('.stock-select');
            loadStocks(productId, stockSelect);
        });

        // Update quantity validation when stock changes
        $(document).on('change', '.stock-select', function() {
            const selectedOption = $(this).find('option:selected');
            const quantityInput = $(this).closest('.product-item').find('.quantity-input');
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
            const originalItem = $('.product-item').first();
            originalItem.find('select').select2('destroy');
            const newItem = originalItem.clone();
            originalItem.find('select').select2();
            
            newItem.find('select').val('').trigger('change');
            newItem.find('input').val('');
            newItem.find('.quantity-hint, .stock-hint').text('');
            newItem.find('.remove-item').show();
            
            const newIndex = itemCount++;
            newItem.find('select, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + newIndex + ']'));
                }
            });
            
            $('#product-items').append(newItem);
            newItem.find('select').select2();
        });

        // Remove item
        $(document).on('click', '.remove-item', function() {
            if ($('.product-item').length > 1) {
                $(this).closest('.product-item').remove();
            }
        });

        // Form submission
        $('#addProductsForm').on('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            let firstInvalidField = null;

            // Validate each item
            $('.product-item').each(function() {
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
        });
    });
</script>
@endpush 