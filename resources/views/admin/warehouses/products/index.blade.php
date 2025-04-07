@extends('admin.partials.master')
@section('title')
    {{ __('Warehouses Products') }}
@endsection

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Products in') }}: {{ $warehouse->name }}</h2>
                    <p class="section-lead">
                        {{ __('You have total') . ' ' . $products->total() . ' ' . __('Products') }}
                    </p>
                </div>
                <div class="buttons mt-3">
                    <a href="{{ route('warehouse.index') }}" class="btn btn-outline-primary mr-2">
                        <i class="fas fa-arrow-left"></i> {{ __('Back') }}
                    </a>
                    @if(hasPermission('warehouse_product_create'))
                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#addProductModal">
                            <i class="fas fa-plus"></i> {{ __('Add Product') }}
                        </button>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ __('All Products') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" action="{{ route('warehouse.products.index', $warehouse->id) }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" placeholder="{{ __('Search') }}" value="{{ @$search }}">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="alert-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
                            @include('admin.warehouses.products.partials.products-table')
                        </div>
                        <div class="card-footer">
                            {{ $products->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('admin.warehouses.products.partials.add-product-modal')
    @include('admin.warehouses.products.partials.edit-product-modal')
    @include('admin.warehouses.products.partials.delete-modal')
@endsection

@push('script')
<script>
    function showAlert(message, type = 'success') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('#alert-container').append(alertHtml);
        
        // Auto remove after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);
    }

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
                        showAlert(xhr.responseJSON.error || '{{ __("Error loading stocks") }}', 'danger');
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
                showAlert('{{ __("Quantity cannot exceed available stock") }}', 'danger');
                return false;
            }
            
            if (quantity > availableSpace) {
                e.preventDefault();
                showAlert('{{ __("Quantity cannot exceed available warehouse space") }}', 'danger');
                return false;
            }
        });

        // Handle edit button click
        $('.edit-product').on('click', function() {
            const id = $(this).data('id');
            const quantity = $(this).data('quantity');
            
            $('#editProductForm').attr('action', '{{ route("warehouse.products.update", ["warehouse" => $warehouse->id, "id" => ":id"]) }}'.replace(':id', id));
            $('#edit_quantity').val(quantity);
            
            $('#editProductModal').modal('show');
        });

        $('.delete-product').click(function() {
            const productId = $(this).data('id');
            const warehouseId = $(this).data('warehouse-id');
            const form = $('#deleteProductForm');
            
            form.attr('action', "{{ route('warehouse.products.destroy', ['warehouse' => ':warehouseId', 'id' => ':productId']) }}"
                .replace(':warehouseId', warehouseId)
                .replace(':productId', productId));
            $('#deleteProductModal').modal('show');
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
                        showAlert(response.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    }
                },
                error: function(xhr) {
                    const errorData = xhr.responseJSON;
                    showAlert(errorData.error || '{{ __("An error occurred") }}', 'danger');
                }
            });
        });
    });
</script>
@endpush 