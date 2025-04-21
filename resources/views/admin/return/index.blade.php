@extends('admin.partials.master')
@section('title', __('Return'))
@section('return', 'active')

@section('main-content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">{{ __('Liste des demandes de retour') }}</h5>
        <div class="float-end">
            <div class="btn-group">
                <!-- <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bx bx-filter-alt"></i> {{ __('Filtrer') }}
                </button> -->
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}">{{ __('En attente') }}</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}">{{ __('Approuvées') }}</a></li>
                    <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}">{{ __('Rejetées') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ request()->url() }}">{{ __('Toutes') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>{{ __('Commande') }}</th>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Raison') }}</th>
                        <th>{{ __('Statut') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnRequests as $request)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('order.view', $request->order_id) }}">
                                #{{ $request->order_id }}
                            </a>
                        </td>
                        <td>{{ $request->user->first_name . ' ' .  $request->user->last_name }}</td>
                        <td class="text-truncate" style="max-width: 200px;" title="{{ $request->reason }}">
                            {{ Str::limit($request->reason, 50) }}
                        </td>
                        <td>
                            <span class="badge bg-{{ $request->status_color }}">
                                {{ __(ucfirst($request->status)) }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group">
                               
                                {{-- <a href="{{ route('admin.return-requests.show', $request->id) }}" 
                                   class="btn btn-sm btn-info" 
                                   title="{{ __('Voir') }}">
                                    <i class="bx bx-show"></i>
                                </a>
                                 --}}

                                 
                                @if($request->status === 'pending')
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bx bx-check"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item" href="#" 
                                               onclick="processReturn({{ $request->id }}, 'approve')">
                                                <i class="bx bx-dollar"></i> {{ __('Rembourser') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" 
                                               onclick="processReturn({{ $request->id }}, 'exchange')">
                                                <i class="bx bx-refresh"></i> {{ __('Échanger') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger" href="#" 
                                               onclick="processReturn({{ $request->id }}, 'reject')">
                                                <i class="bx bx-x"></i> {{ __('Rejeter') }}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            {{ __('Aucune demande de retour trouvée') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $returnRequests->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function processReturn(requestId, action) {
    Swal.fire({
        title: '{{ __("Confirmer l\'action") }}',
        input: action === 'reject' ? 'text' : null,
        inputLabel: action === 'reject' ? '{{ __("Raison du rejet") }}' : null,
        showCancelButton: true,
        confirmButtonText: '{{ __("Confirmer") }}',
        cancelButtonText: '{{ __("Annuler") }}',
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(`/admin/return/${requestId}/process`, {
                action: action,
                notes: result.value || null
            }).then(response => {
                Swal.fire('Succès!', response.data.message, 'success')
                    .then(() => window.location.reload());
            }).catch(error => {
                Swal.fire('Erreur!', error.response.data.message, 'error');
            });
        }
    });
}
</script>
@endpush