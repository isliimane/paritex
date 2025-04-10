
 @extends('admin.partials.master')
@section('title', __('Claims'))
@section('claim', 'active')

@section('main-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('All Claims') }}</h4>
                       
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($claims) && $claims->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Subject') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($claims as $claim)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $claim->user->name ?? __('N/A') }}</td>
                                        <td>{{ Str::limit($claim->subject, 30) }}</td>
                                        <td>
                                            <span class="badge">
                                                {{ ucfirst(str_replace('_', ' ', $claim->status)) }}
                                            </span>
                                        </td>
                                        <td>{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="d-flex">
                                                @if(hasPermission('claim_read'))
                                                <a href="{{ route('admin.claim.show', $claim->id) }}" 
                                                   class="btn btn-sm btn-info me-1"
                                                   title="{{ __('View') }}">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                @endif
                                                
                                               
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $claims->links() }}
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bx bx-error"></i> {{ __('No claims found') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection  