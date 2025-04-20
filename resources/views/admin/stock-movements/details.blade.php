<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Warehouse') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Variant') }}</th>
                <th>{{ __('Quantity') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('User') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->warehouse->getTranslation('name', \App::getLocale()) }}</td>
                    <td>{{ $movement->product->getTranslation('name', \App::getLocale()) }}</td>
                    <td>{{ $movement->productStock?->name }}</td>
                    <td>
                        <span class="badge badge-{{ $movement->movement_type === 'in' ? 'success' : 'danger' }}">
                            {{ $movement->movement_type === 'in' ? '+' : '-' }}{{ $movement->quantity }}
                        </span>
                    </td>
                    <td>{{ $movement->movement_type_label }}</td>
                    <td>{{ $movement->user->first_name }} {{ $movement->user->last_name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div> 