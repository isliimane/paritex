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
                        <a href="{{ route('warehouse.products.create', $warehouse->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-plus"></i> {{ __('Add Product') }}
                        </a>
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

    @include('admin.warehouses.products.partials.edit-product-modal')
    @include('admin.warehouses.products.partials.delete-modal')
@endsection

@push('script')
<script>
        let clickedButton = null;

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
        // Handle edit button click
        $('.edit-product').on('click', function() {
            const id = $(this).data('id');
            const quantity = $(this).data('quantity');
            
            $('#editProductForm').attr('action', '{{ route("warehouse.products.update", ["warehouse" => $warehouse->id, "id" => ":id"]) }}'.replace(':id', id));
            $('#edit_quantity').val(quantity);
             clickedButton = $(this);
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
        $('#editProductForm').on('submit', function(e) {
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