@extends('admin.partials.master')
@section('title', __('Claim Details'))
@section('claim', 'active')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bx bx-chevron-left"></i>
                            <a href="{{ route('admin.claim.index') }}" class="text-dark">
                                {{ __('Back to Claims') }}
                            </a>
                        </h4>
                        <div>
                            <span class="badge">
                                {{ ucfirst(str_replace('_', ' ', $claim->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="mb-3">{{ __('Claim Information') }}</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">{{ __('User') }}</th>
                                        <td>{{ $claim->user->name ?? __('N/A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Subject') }}</th>
                                        <td>{{ $claim->subject }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Order Reference') }}</th>
                                        <td>{{ $claim->order->code ?? __('N/A') }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ __('Submitted On') }}</th>
                                        <td>{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">{{ __('Description') }}</h5>
                            <div class="border p-3 bg-light rounded">
                                {!! nl2br(e($claim->description)) !!}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <form action="{{ route('admin.claim.update', $claim->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Status') }}</label>
                                    <select name="status" class="form-select" required>
                                        @foreach(['pending', 'in_progress', 'resolved', 'rejected'] as $status)
                                            <option value="{{ $status }}" {{ $claim->status == $status ? 'selected' : '' }}>
                                                {{ __(ucfirst(str_replace('_', ' ', $status))) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">{{ __('Last Updated') }}</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $claim->updated_at->diffForHumans() }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <label class="form-label">{{ __('Admin Response') }}</label>
                            <textarea name="admin_response" class="form-control" 
                                      rows="4" placeholder="{{ __('Enter your response here...') }}"
                            >{{ old('admin_response', $claim->admin_response) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bx bx-save"></i> {{ __('Update Claim') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Scripts supplémentaires si nécessaire
    $(document).ready(function() {
        console.log('Claim details page loaded');
    });
</script>
@endpush