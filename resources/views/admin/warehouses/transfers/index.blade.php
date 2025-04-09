@extends('admin.partials.master')

@section('title', __('Warehouse Transfers'))

@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                        <div class="d-block">
                            <h2 class="section-title">{{ __('Transfers') }}</h2>
                            <p class="section-lead">
                                {{ __('You have total') . ' ' . $transfers->total() . ' ' . __('Transfers') }}
                            </p>
                        </div>
                        <div class="buttons mt-3">
                            @if(hasPermission('warehouse_transfer_create'))
                                <a href="{{ route('transfers.create') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-plus"></i> {{ __('New Transfer') }}
                                </a>
                            @endif
                        </div>
                    </div>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">

                        <h4>{{ __('All Transfers') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" action="{{ route('transfers.index') }}" method="GET">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="search" name="search" placeholder="{{ __('Search') }}" value="{{ request('search') }}">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-outline-primary"><i class="bx bx-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>{{ __('ID') }}</th>
                                            <th>{{ __('From Warehouse') }}</th>
                                            <th>{{ __('To Warehouse') }}</th>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Quantity') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Created By') }}</th>
                                            <th>{{ __('Created At') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transfers as $transfer)
                                            <tr>
                                                <td>{{ $transfer->id }}</td>
                                                <td>{{ $transfer->fromWarehouse->getTranslation('name', \App::getLocale()) }}</td>
                                                <td>{{ $transfer->toWarehouse->getTranslation('name', \App::getLocale()) }}</td>
                                                <td>{{ $transfer->product->getTranslation('name', \App::getLocale()) }}</td>
                                                <td>{{ $transfer->quantity }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $transfer->status === 'pending' ? 'warning' : ($transfer->status === 'completed' ? 'success' : 'danger') }}">
                                                        {{ ucfirst($transfer->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $transfer->createdBy->first_name }} {{ $transfer->createdBy->last_name }}</td>
                                                <td>{{ $transfer->created_at->format('Y-m-d H:i') }}</td>
                                                <td style="white-space: nowrap;">
                                                    @if($transfer->status === 'pending')
                                                        @if(hasPermission('warehouse_transfer_approve'))
                                                            <form action="{{ route('transfers.approve', $transfer->id) }}" method="POST" style="display: inline;" class="approve-form">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                        data-toggle="tooltip" data-placement="top" title="{{ __('Approve') }}">
                                                                    <i class="bx bx-check"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                        @if(hasPermission('warehouse_transfer_reject'))
                                                            <form action="{{ route('transfers.reject', $transfer->id) }}" method="POST" style="display: inline;" class="reject-form">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                        data-toggle="tooltip" data-placement="top" title="{{ __('Reject') }}">
                                                                    <i class="bx bx-x"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @else
                                                        ---
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">{{ __('No transfers found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">
                                {{ $transfers->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('script')
<!-- <script src="{{ static_asset('admin/js/sweetalert2.all.min.js') }}"></script> -->
<script>
    $(document).ready(function() {
        // Confirmation for Approving
        $('.approve-form').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            var form = this; // Store the form element

            Swal.fire({
                title: '{{ __("Are you sure?") }}',
                text: '{{ __("You are about to approve this transfer.") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("Yes, approve it!") }}',
                cancelButtonText: '{{ __("Cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                }
            });
        });

        // Confirmation for Rejecting
        $('.reject-form').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            var form = this;

            Swal.fire({
                title: '{{ __("Are you sure?") }}',
                text: '{{ __("You are about to reject this transfer.") }}',
                icon: 'error', // Use error icon for rejection
                showCancelButton: true,
                confirmButtonColor: '#d33', // Use danger color for confirm
                cancelButtonColor: '#3085d6',
                confirmButtonText: '{{ __("Yes, reject it!") }}',
                cancelButtonText: '{{ __("Cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Submit the form if confirmed
                }
            });
        });
    });
</script>
@endpush 