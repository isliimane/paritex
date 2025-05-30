<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\SendMailTrait;
use App\Traits\SendNotification;
use App\Utility\AppSettingUtility;
use Illuminate\Support\Facades\DB;
use App\Models\RegistrationRequest;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Requests\User\SignUpRequest;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Sentinel;

class RegisterController extends Controller
{
    use SendMailTrait,SendNotification;

    public function register()
    {

        return view('admin.authenticate.register');
    }

    public function postRegister(SignUpRequest $request)
    {
        if (config('app.demo_mode')) {
            return response()->json([
                'error' => __('This function is disabled in demo server.')
            ]);
        }
        DB::beginTransaction();
        try {
            
            if ($request->phone) {
                $request['phone'] = str_replace(' ','',$request->phone);
                $request['password'] = '123456';
                $sellerData = Sentinel::registerAndActivate($request->all());
            }

                if (!$request->phone) {
                    if (settingHelper('disable_email_confirmation') == 1)
                    {
                        $sellerData = Sentinel::registerAndActivate($request->all());
                        
                    }
                    else{

                        $sellerData = Sentinel::register($request->all());
                        $activation = Activation::create($sellerData);
                    }
                }
            
            if ($request->email) {
                if (settingHelper('disable_email_confirmation') != 1)
                {
                    $this->sendmail($request->email, 'Registration', $sellerData, 'email.auth.activate-account-email',url('/') . '/activation/' . $request->email . '/' . $activation->code);
                }
                if($sellerData){
                    $user_id = $sellerData->id;
                    $sellerData['id'] = 1;
                    $this->SendNotification($sellerData, __("{$sellerData->first_name} {$sellerData->last_name} has been registered"),'success','edit-customer/'.$user_id);
                }
            }
            else if ($request->phone){
                $user = User::where('phone',$request->phone)->first();
                Sentinel::login($user);
            }


            $request['user_id'] = $sellerData->id;
            DB::commit();

            return response()->json([
                'success' => $request->email && settingHelper('disable_email_confirmation') != 1 ?  __('Check your mail to verify your account') : __('Registration Successfully'),
                'migrate_msg' => __('Request sent successfully. Wait for approval.'),
                'user' => $sellerData,
                'auth_user' => authUser(),
                'type' => $request->email ? 0 : 1,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }

}
