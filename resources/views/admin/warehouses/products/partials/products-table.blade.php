<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Stock') }}</th>
                <th>{{ __('Quantity') }}</th>
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
                    <td>
                        @if(hasPermission('warehouse_products.edit'))
                            <button type="button" class="btn btn-outline-secondary btn-circle edit-product" 
                                    data-id="{{ $product->id }}"
                                    data-quantity="{{ $product->quantity }}"
                                    data-toggle="tooltip" title="" data-original-title="{{ __('Edit') }}">
                                <i class="bx bx-edit"></i>
                            </button>
                        @endif
                        @if(hasPermission('warehouse_products.delete'))
                            <button type="button" 
                                    class="btn btn-outline-danger btn-circle delete-product" 
                                    data-id="{{ $product->id }}"
                                    data-warehouse-id="{{ $warehouse->id }}"
                                    data-toggle="tooltip" 
                                    title=""
                                    data-original-title="{{ __('Delete') }}">
                                <i class="bx bx-trash"></i>
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">{{ __('No products found in this warehouse') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $products->links() }}
</div>
