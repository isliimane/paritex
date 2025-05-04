<?php

namespace App\Repositories\Site;

use App\Models\Complaint;
use App\Repositories\Interfaces\Site\ComplaintInterface;
use App\Traits\SendMailTrait;
use Sentinel;

class ComplaintRepository implements ComplaintInterface {

    //for APi
    use SendMailTrait;
    public function paginate($paginate)
    {
        return Complaint::latest()->paginate($paginate);
    }

    public function storeComplaint($request)
    {
        $complaint = Complaint::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'subject'   => $request->subject,
            'message'   => $request->message,
        ]);

        // $data['message']     = [
        //     'message_from' => 'Message From: '.$request->name,
        //     'email' => 'Email: '.$request->email,
        //     'message' => 'Message: '.$request->message,
        // ];
        // $data['subject']     = $request->subject;
        // $admin = User::find(1);
        // $this->sendmail($admin->email, 'Complaint', $data, 'email.auth.email-template','');
        
        return $complaint;
    }

    public function reply($request)
    {
        $complaint = Complaint::find($request['id']);
        $data['subject'] = $request['subject'];
        $data['message'] = $request['reply'];
//        sendMailTo($request['email'], $data);
        $this->sendmail($request['email'], 'Complaint', $data, 'email.auth.email-template','');
        $complaint->update($request);
        return $complaint;
    }
}
