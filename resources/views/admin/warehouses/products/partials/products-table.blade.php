<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Stock') }}</th>
                <th>{{ __('Quantity') }}</th>
                <th>{{ __('Shelf') }}</th>
                <th>{{ __('Column') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->id }}</td>
                    <td>{{ $product->product->getTranslation('name', \App::getLocale()) }}</td>
                    <td>{{ $product->productStock->sku ?? '-' }}</td>
                    <td>{{ $product->quantity }}</td>
                    <td>{{ $product->shelf_number ?? '-' }}</td>
                    <td>{{ $product->column_number ?? '-' }}</td>
                    <td>
                        @if(hasPermission('warehouse_products.edit'))
                            <button type="button" class="btn btn-primary btn-sm edit-product" 
                                    data-id="{{ $product->id }}"
                                    data-product-id="{{ $product->product_id }}"
                                    data-stock-id="{{ $product->product_stock_id }}"
                                    data-quantity="{{ $product->quantity }}"
                                    data-shelf="{{ $product->shelf_number }}"
                                    data-column="{{ $product->column_number }}">
                                <i class="fas fa-edit"></i>
                            </button>
                        @endif
                        @if(hasPermission('warehouse_products.delete'))
                            <form action="{{ route('warehouse.products.destroy', [$warehouse->id, $product->id]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('Are you sure?') }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">{{ __('No products found in this warehouse') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">
    {{ $products->links() }} 