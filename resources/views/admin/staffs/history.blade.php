@extends('admin.partials.master')

@section('title')
    {{ __('Staff History') }}
@endsection
@php
    $dt               = isset($_GET['dt']) ? $_GET['dt'] : null;
@endphp
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Activity History for') }} {{ $staff->name }}</h2>
                    <p class="section-lead">
                        {{ __('Total') . ' ' . $logs->total() . ' ' . __('Activities') }}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-xs-12 col-md-12">
                    <div class="card">
                        <div class="card-header justify-content-between">
                            <h4>{{ __('Filter By Date') }}</h4>
                            <div class="card-header-form">
                                <form class="form-inline" method="GET">
                                    <div class="form-group col-sm-xs-12 col-md-5">
                                        <label for="time_period">{{ __('Filter by Date Range') }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="bx bx-calendar"></i>
                                                </div>
                                            </div>
                                            <input type="text" name="dt" id="dt"
                                                   value="{{ @$dt }}"
                                                   placeholder="{{ __('Filter by Date Range') }}"
                                                   class="form-control reportrange">
                                        </div>
                                        @if ($errors->has('dt'))
                                            <div class="invalid-feedback">
                                                <p>{{ $errors->first('dt') }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    <button class="btn btn-outline-primary"><i class="bx bx-filter"></i> {{ __('Filter') }}</button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Action') }}</th>
                                            <th>{{ __('Subject') }}</th>
                                            <th>{{ __('Description') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($logs as $key => $log)
                                            <tr>
                                                <td>{{ $logs->firstItem() + $key }}</td>
                                                <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $log->action)) }}</td>
                                                <td>{{ $log->subject_type }} {{ $log->subject_id ? "(ID: $log->subject_id)" : '' }}</td>
                                                <td>{{ getActivityDescription($log->action) }}
                                                    @if($log->subject_type && $log->subject_id && getActivityLink($log->action, $log->subject_type, $log->subject_id))
                                                        <br>
                                                        <a href="{{ getActivityLink($log->action, $log->subject_type, $log->subject_id) }}"
                                                        class="btn btn-sm btn-outline-primary mt-1"
                                                        target="_blank">
                                                            {{ __('Consult') }}
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center">{{ __('No logs found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card-footer">
                            <nav class="d-inline-block">
                                {{ $logs->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
