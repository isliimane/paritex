<?php

namespace App\Repositories\Site;

use App\Models\Complaint;
use App\Repositories\Interfaces\Site\ComplaintInterface;
use App\Traits\SendMailTrait;
use Sentinel;
use App\Models\Support;

class ComplaintRepository implements ComplaintInterface {

    //for APi
    use SendMailTrait;
    public function paginate($paginate)
    {
        return Complaint::latest()->paginate($paginate);
    }

    public function storeComplaint($request)
    {
        $support = new Support();
        $support->subject           = $request->subject;
        $support->user_id           = authId();
        $support->ticket_id         = rand(1000,50000);
        $support->support_department_id     = $request->support_department_id;
        $support->priority          = $request->priority;
        $support->status            = "pending";
        $support->ticket_body       = $request->message;
        if(!blank($request->file)){

        $array_file = explode(',', $request->file);
        $all_files= [];
        foreach ($array_file as $key => $array){
            $files = $this->getAllType($array);
            if ($files):
                array_push($all_files, $files);
            else:
                unset($array_file[$key]);
            endif;
        }
        $support->file     = $all_files;
        }

        $support->save();
        return true;
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
