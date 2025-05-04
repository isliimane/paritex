@extends('admin.partials.master')

@section('title')
    {{ __('Complaints') }}
@endsection
@section('support_active')
    active
@endsection
@section('Complaints')
    active
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{__('Complaints')}}</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-md">
                                    <tbody>
                                    <tr>
                                        <th>#</th>
                                        <th>{{__('Name')}}</th>
                                        <th>{{__('Email')}}</th>
                                        <th>{{__('Message')}}</th>
                                        <th>{{__('Sending Date')}}</th>
                                        <th>{{__('Replay')}}</th>
                                    </tr>


                                    @foreach ($complaints as $key => $complaint)
                                        <tr id="row_{{$complaint->id}}">
                                            <td>{{$complaints->firstItem() + $key}}</td>
                                            <td>{{$complaint->name}}
                                            @if ($complaint->reply)
                                                <span class="badge badge-success">{{ __('replied')}}</span>
                                            @endif
                                            </td>
                                            <td>{{$complaint->email}}</td>
                                            <td>{{$complaint->message}}</td>
                                            <td>{{ \Carbon\Carbon::parse($complaint->updated_at)}}</td>
                                            <td>
                                                <a href="javascript:void(0)" class="btn btn-outline-primary btn-circle modal-menu"
                                                   data-title="{{__('Replay Message')}}"
                                                   data-url="{{ route('edit-info', ['page_name' => 'complaint-replay','param1' => $complaint->id]) }}"
                                                   data-toggle="modal" data-target="#common-modal"
                                                   data-original-title="{{ __('Replay') }}"><i class='bx bx-reply'></i>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-outline-primary btn-circle modal-menu"
                                                   data-title="{{__('View Message')}}"
                                                   data-url="{{ route('edit-info', ['page_name' => 'view-complaint','param1' => $complaint->id]) }}"
                                                   data-toggle="modal" data-target="#common-modal"
                                                   data-original-title="{{ __('Replay') }}"><i class='bx bx-show'></i>
                                                </a>
                                                <a href="javascript:void(0)"
                                                   onclick="delete_row('delete/complaints/',{{$complaint->id}})"
                                                   class="btn btn-outline-danger btn-circle" data-toggle="tooltip"
                                                   title=""
                                                   data-original-title="{{ __('Delete') }}"><i class="bx bx-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <nav class="d-inline-block">
                                {{ $complaints->appends(Request::except('page'))->links('pagination::bootstrap-4') }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('admin.common.common-modal')
@include('admin.common.delete-ajax')



