
@php
    $complaint = App\Models\Complaint::find($otherLinks[0]);
@endphp
<form action="{{route('complaints.replay',$complaint->id)}}" method="post">
    @csrf
    @method('put')
    <div class="modal-body modal-padding-bottom modal-body-overflow-unset">
        <div class="form-group align-items-center">
            <label for="email" class="form-control-label">{{ __('Email') }}</label>
            <input type="text" name="email" placeholder="email" readonly value="{{ $complaint->email }}" class="form-control" id="tax-name" required />
        </div>
        <div class="form-group align-items-center">
            <label for="subject" class="form-control-label">{{ __('Subject') }}</label>
            <input type="text" name="subject" placeholder="{{__('Subject')}}" value="" class="form-control" id="tax-name" required autofocus/>
        </div>
        <div class="form-group align-items-center">
            <label for="reply" class="form-control-label">{{ __('Message') }}</label>
            <input type="hidden" name="id" value="{{$complaint->id}}"/>
            <textarea type="text" name="reply" placeholder="Replay" class="form-control"  required></textarea>
        </div>
    </div>
    <div class="modal-footer bg-whitesmoke br modal-padding-bottom ">
        <button type="submit" class="btn btn-outline-primary">{{ __('Send') }}</button>
    </div>
</form>
