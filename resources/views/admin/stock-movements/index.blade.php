@extends('admin.partials.master')

@section('title', __('Stock Movements'))

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Stock Movements') }}</h2>
                    <p class="section-lead">
                        {{ __('Track all stock movements in warehouses') }}
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-exchange-alt"></i> {{ __('Stock Movements') }}</h4>
                            <div class="card-header-form">
                                <form action="{{ route('stock-movements.index') }}" method="GET">
                                    <div class="input-group align-items-center">
                                        <select name="warehouse_id" class="form-control select2">
                                            <option value="">{{ __('All Warehouses') }}</option>
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                                    {{ $warehouse->getTranslation('name', \App::getLocale()) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <select name="movement_type" class="form-control selectric">
                                            <option value="">{{ __('All Types') }}</option>
                                            <option value="in" {{ request('movement_type') == 'in' ? 'selected' : '' }}>{{ __('In') }}</option>
                                            <option value="out" {{ request('movement_type') == 'out' ? 'selected' : '' }}>{{ __('Out') }}</option>
                                        </select>
                                        <select name="movement_reason" class="form-control selectric">
                                            <option value="">{{ __('All Reasons') }}</option>
                                            <option value="order" {{ request('movement_reason') == 'order' ? 'selected' : '' }}>{{ __('Order') }}</option>
                                            <option value="transfer" {{ request('movement_reason') == 'transfer' ? 'selected' : '' }}>{{ __('Transfer') }}</option>
                                            <option value="cancel" {{ request('movement_reason') == 'cancel' ? 'selected' : '' }}>{{ __('Cancel') }}</option>
                                            <!-- <option value="manual" {{ request('movement_reason') == 'manual' ? 'selected' : '' }}>{{ __('Manual') }}</option> -->
                                            <option value="refund" {{ request('movement_reason') == 'refund' ? 'selected' : '' }}>{{ __('Refund') }}</option>
                                        
                                        </select>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="bx bx-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Reference') }}</th>
                                            <th>{{ __('Reason') }}</th>
                                            <th>{{ __('Warehouse') }}</th>
                                            <th>{{ __('Total Movements') }}</th>
                                            <!-- <th>{{ __('Net Quantity') }}</th> -->
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($movements as $movement)
                                            <tr>
                                                <td>{{ $movement->related_id }}</td>
                                                <td>{{ $movement->movement_reason_label }}</td>
                                                <td>
                                                    @if($movement->movement_reason === 'transfer')
                                                        {{ $movement->transfer->fromWarehouse->getTranslation('name', \App::getLocale()) }} → {{ $movement->transfer->toWarehouse->getTranslation('name', \App::getLocale()) }}
                                                    @else
                                                        {{ $movement->warehouse->getTranslation('name', \App::getLocale()) }}
                                                    @endif
                                                </td>
                                                <td>{{ $movement->total_movements }}</td>
                                                <!-- <td>
                                                    <span class="badge badge-{{ $movement->net_quantity >= 0 ? 'success' : 'danger' }}">
                                                        {{ $movement->net_quantity >= 0 ? '+' : '' }}{{ $movement->net_quantity }}
                                                    </span>
                                                </td> -->
                                                <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                                                <td>
                                                    <a href="javascript:void(0)"
                                                    class="btn btn-outline-info btn-circle show-details-btn"
                                                    data-related-id="{{ $movement->related_id }}"
                                                    data-movement-reason="{{ $movement->movement_reason }}"
                                                    data-toggle="tooltip" title=""
                                                    data-original-title="{{ __('View') }}">
                                                        <i class="bx bx-show"></i> 
                                                </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="float-right">
                                {{ $movements->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailsModalLabel">{{ __('Movement Details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .select2.select2-container.select2-container--default {
            width: 200px !important;
        }
    </style>
@endpush

@push('script')
<script>
$(document).ready(function() {
    $('.show-details-btn').on('click', function() {
        console.log('clicked');
        var relatedId = $(this).data('related-id');
        var movementReason = $(this).data('movement-reason');
        
        $.get('{{ route("admin.stock-movements.details") }}', {
            related_id: relatedId,
            movement_reason: movementReason
        }, function(data) {
            $('#detailsContent').html(data);
            $('#detailsModal').modal('show');
        });
    });
});
</script>
@endpush 