<?php

namespace App\Repositories\Site;

use App\Models\ContactUs;
use App\Repositories\Interfaces\Site\ContactUsInterface;
use App\Traits\SendMailTrait;
use Sentinel;

class ContactUsRepository implements ContactUsInterface {

    //for APi
    use SendMailTrait;
    public function paginate($paginate)
    {
        return ContactUs::latest()->paginate($paginate);
    }

    public function storeContact($request)
    {
        $contact = ContactUs::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'subject'   => $request->subject,
            'message'   => $request->message,
        ]);

            $data['message']     = [
                'message_from' => 'Message From: '.$request->name,
                'email' => 'Email: '.$request->email,
                'message' => 'Message: '.$request->message,
            ];
            $data['subject']     = $request->subject;
            $user = User::find(1);
            $this->sendmail($user->email, 'Contact', $data, 'email.auth.email-template','');

        return $contact;
    }

    public function reply($request)
    {
        $contact = ContactUs::find($request['id']);
        $data['subject'] = $request['subject'];
        $data['message'] = $request['reply'];
//        sendMailTo($request['email'], $data);
        $this->sendmail($request['email'], 'Contact', $data, 'email.auth.email-template','');
        $contact->update($request);
        return $contact;
    }
}
