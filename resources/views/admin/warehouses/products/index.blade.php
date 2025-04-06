@extends('admin.partials.master')
@section('title')
    {{ __('Warehouses Products') }}
@endsection

@section('main-content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('warehouse.index') }}" class="btn btn-secondary btn-sm mr-2">
                                    <i class="fas fa-arrow-left"></i> {{ __('Back to Warehouses') }}
                                </a>
                                <h4 class="card-title d-inline-block">{{ __('Products in Warehouse') }}: {{ $warehouse->name }}</h4>
                            </div>
                            <div class="card-tools">
                                @if(hasPermission('warehouse_product_create'))
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addProductModal">
                                        <i class="fas fa-plus"></i> {{ __('Add Product') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('admin.warehouses.products.partials.products-table')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.warehouses.products.partials.add-product-modal')
    @include('admin.warehouses.products.partials.edit-product-modal')
@endsection

@push('script')
<script>
    $(document).ready(function() {
        // Load stocks when product is selected
        $('#product_id').on('change', function() {
            const productId = $(this).val();
            const $stockSelect = $('#product_stock_id');
            const $stockInfo = $('#stockInfo');
            
            $stockSelect.empty().append('<option value="">{{ __('Select Stock') }}</option>');
            $stockInfo.text('');
            
            if (productId) {
                const url = '{{ route("warehouse.products.products.stocks", ["warehouse" => $warehouse->id, "product" => ":productId"]) }}'.replace(':productId', productId);
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response && response.length > 0) {
                            response.forEach(function(stock) {
                                $stockSelect.append(
                                    `<option value="${stock.id}" data-quantity="${stock.available_quantity}">
                                        ${stock.sku} ({{ __('Available:') }} ${stock.available_quantity})
                                    </option>`
                                );
                            });
                        } else {
                            $stockInfo.text('{{ __("No stocks available for this product") }}');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading stocks:', xhr);
                        console.error('Response:', xhr.responseText);
                        alert('{{ __("Error loading stocks") }}');
                    }
                });
            }
        });

        // Update available quantity when stock is selected
        $('#product_stock_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const availableQuantity = selectedOption.data('quantity') || 0;
            $('#stockInfo').text(`{{ __('Available Quantity:') }} ${availableQuantity}`);
            $('#quantity').attr('max', availableQuantity);
        });

        // Validate form before submission
        $('#addProductForm').on('submit', function(e) {
            const quantity = parseInt($('#quantity').val());
            const availableQuantity = parseInt($('#product_stock_id option:selected').data('quantity') || 0);
            const availableSpace = parseInt($('#availableSpace').text());
            
            if (quantity > availableQuantity) {
                e.preventDefault();
                alert('{{ __("Quantity cannot exceed available stock") }}');
                return false;
            }
            
            if (quantity > availableSpace) {
                e.preventDefault();
                alert('{{ __("Quantity cannot exceed available warehouse space") }}');
                return false;
            }
        });

        // Handle edit button click
        $('.edit-product').on('click', function() {
            const id = $(this).data('id');
            const quantity = $(this).data('quantity');
            const shelf = $(this).data('shelf');
            const column = $(this).data('column');
            
            $('#editProductForm').attr('action', '{{ route("warehouse.products.update", ["warehouse" => $warehouse->id, "id" => ":id"]) }}'.replace(':id', id));
            $('#edit_quantity').val(quantity);
            $('#edit_shelf_number').val(shelf);
            $('#edit_column_number').val(column);
            
            $('#editProductModal').modal('show');
        });

        // Handle form submissions
        $('#addProductForm, #editProductForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || '{{ __("An error occurred") }}');
                }
            });
        });
    });
</script>
@endpush 