@extends('admin.partials.master')
@section('title', __('Détails de la demande de retour'))

@section('main-content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h4 class="card-title mb-4">{{ __('Détails de la demande de retour') }}</h4>
                
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">{{ __('Numéro de commande') }}:</div>
                    <div class="col-md-8">
                        <a href="{{ route('order.view', $returnRequest->order_id) }}">
                            #{{ $returnRequest->order_id }}
                        </a>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">{{ __('Client') }}:</div>
                    <div class="col-md-8">
                        {{ $returnRequest->user->first_name . ' ' . $returnRequest->user->last_name }}
                        <br>
                        <a href="mailto:{{ $returnRequest->user->email }}">{{ $returnRequest->user->email }}</a>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">{{ __('Statut') }}:</div>
                    <div class="col-md-8">
                        <span class="badge bg-{{ $returnRequest->status_color }}">
                            {{ __(ucfirst($returnRequest->status)) }}
                        </span>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">{{ __('Date de la demande') }}:</div>
                    <div class="col-md-8">
                        {{ $returnRequest->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">{{ __('Raison du retour') }}:</div>
                    <div class="col-md-8">
                        {{ $returnRequest->reason }}
                    </div>
                </div>
                
                @if($returnRequest->notes)
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">{{ __('Notes') }}:</div>
                    <div class="col-md-8">
                        {{ $returnRequest->notes }}
                    </div>
                </div>
                @endif
                
                @if($returnRequest->products->count() > 0)
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">{{ __('Produits concernés') }}:</div>
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Produit') }}</th>
                                        <th>{{ __('Quantité') }}</th>
                                        <th>{{ __('Prix') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($returnRequest->products as $product)
                                    <tr>
                                        <td>
                                            {{ $product->name }}
                                            @if($product->pivot->variant)
                                                <br><small>{{ $product->pivot->variant }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $product->pivot->quantity }}</td>
                                        <td>{{ format_price($product->pivot->price) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('Actions') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($returnRequest->status === 'pending')
                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-success" 
                                    onclick="processReturn({{ $returnRequest->id }}, 'approve')">
                                <i class="bx bx-dollar"></i> {{ __('Rembourser') }}
                            </button>
                            
                            <button class="btn btn-primary" 
                                    onclick="processReturn({{ $returnRequest->id }}, 'exchange')">
                                <i class="bx bx-refresh"></i> {{ __('Échanger') }}
                            </button>
                            
                            <button class="btn btn-danger" 
                                    onclick="processReturn({{ $returnRequest->id }}, 'reject')">
                                <i class="bx bx-x"></i> {{ __('Rejeter') }}
                            </button>
                        </div>
                        @endif
                        
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.return-requests.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ __('Retour à la liste') }}
                            </a>
                        </div>
                    </div>
                </div>
                
                @if($returnRequest->status !== 'pending')
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title">{{ __('Historique') }}</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>{{ __('Traitée le') }}:</strong> 
                            {{ $returnRequest->updated_at->format('d/m/Y H:i') }}
                        </p>
                        <p>
                            <strong>{{ __('Par') }}:</strong> 
                            {{ $returnRequest->processedBy->name ?? __('Système') }}
                        </p>
                    </div>
                </div>
                @endif
            </div>
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
        inputPlaceholder: action === 'reject' ? '{{ __("Optionnel") }}' : null,
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