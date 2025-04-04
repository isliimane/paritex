<?php

namespace App\Http\Controllers\Admin\Addons;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Repositories\Admin\Addon\PackageRepository;
use App\Repositories\Admin\Addon\SellerSubscriptionRepository;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\SellerInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PackageController extends Controller
{
    protected $sellerPackage,$sellerSubscription,$seller;

    public function __construct(PackageRepository $sellerPackage,SellerSubscriptionRepository $sellerSubscription,SellerInterface $seller)
    {
        $this->sellerPackage = $sellerPackage;
        $this->sellerSubscription = $sellerSubscription;
        $this->seller = $seller;
    }

    public function subscriptionSetting()
    {
        return view('admin.seller_packages.subscription-setting');
    }

    public function index()
    {
        try {
            $data = [
                'packages' => $this->sellerPackage->paginate(get_pagination('index_form_paginate'))
            ];
            return view('admin.seller_packages.index', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function create()
    {
        try {
            return view('admin.seller_packages.form');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function store(PackageRequest $request): \Illuminate\Http\RedirectResponse
    {
        try {
            if (isDemoServer()):
                Toastr::error(__('This function is disabled in demo server.'),__('Ops..!'));
            endif;
            DB::beginTransaction();
            $this->sellerPackage->store($request->all());
            DB::commit();
            Toastr::success(__('package_created_successfully'));
            return redirect()->route('seller_packages.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function edit($id,LanguageInterface $language,Request $request)
    {
        try {
            $data = [
                'edit'              => $this->sellerPackage->find($id),
                'languages'         => $language->all()->orderBy('id', 'asc')->get(),
                'lang'              => $request->lang ? : app()->getLocale(),
                'r'                 => $request->r != ''? $request->r : $request->server('HTTP_REFERER'),
            ];

            $data['package_language'] = $this->sellerPackage->getByLang($id, $data['lang']);

            return view('admin.seller_packages.form', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function update(PackageRequest $request, $id): \Illuminate\Http\RedirectResponse
    {
        try {
            if (isDemoServer()):
                Toastr::error(__('This function is disabled in demo server.'),__('Ops..!'));
            endif;
            DB::beginTransaction();
            $this->sellerPackage->update($request->all(),$id);
            DB::commit();
            Toastr::success(__('package_updated_successfully'));
            return redirect()->route('seller_packages.index');
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function destroy($id): \Illuminate\Http\RedirectResponse
    {
        DB::beginTransaction();
        try {
            if (isDemoServer()):
                Toastr::error(__('This function is disabled in demo server.'),__('Ops..!'));
            endif;
            $this->sellerPackage->destroy($id);
            Toastr::success(__('package_deleted_successfully'));
            DB::commit();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function statusChange(Request $request): \Illuminate\Http\JsonResponse
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $this->sellerPackage->statusChange($request['data']);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message']    = $e->getMessage();
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        }
    }

    public function subscriptionStatusChange(Request $request): \Illuminate\Http\JsonResponse
    {
        if (isDemoServer()):
            $response['message']    = __('This function is disabled in demo server.');
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        endif;

        DB::beginTransaction();
        try {
            $data = $request->data;
            $this->sellerSubscription->statusChange($data);
            $response['message']    = __('Updated Successfully');
            $response['title']      = __('Success');
            $response['status']     = 'success';
            DB::commit();
            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            $response['message']    = $e->getMessage();
            $response['title']      = __('Ops..!');
            $response['status']     = 'error';
            return response()->json($response);
        }
    }

    public function offlinePurchaseHistory(Request $request)
    {
        try {
            $input = $request->all();
            $input['offline'] = 'offline';
            $seller = $request->user_id ? $this->seller->getSeller($request->user_id) : null;

            if ($seller)
            {
                $input['user_id'] = $seller->user_id;
            }

            $sorting = $request->s;

            if (!$sorting)
            {
                $sorting = 'latest_on_top';
            }

            $data = [
                'purchases' => $this->sellerSubscription->paginate(get_pagination('index_form_paginate'),$input),
                'seller'    => $seller,
                's'         => $sorting,
                'q'         => $request->q,
            ];
            return view('seller.packages.offline_purchase_history', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function onlinePurchaseHistory(Request $request)
    {
        try {
            $input = $request->all();

            $seller = $request->user_id ? $this->seller->getSeller($request->user_id) : null;

            if ($seller)
            {
                $input['user_id'] = $seller->user_id;
            }

            $sorting = $request->s;

            if (!$sorting)
            {
                $sorting = 'latest_on_top';
            }

            $input['s'] = $sorting;


            $data = [
                'purchases' => $this->sellerSubscription->paginate(get_pagination('index_form_paginate'),$input),
                'seller'    => $seller,
                's'         => $sorting,
                'q'         => $request->q,
            ];
            return view('seller.packages.online_purchase_history', $data);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function cronSubscription(): \Illuminate\Http\RedirectResponse
    {
        try {
            Artisan::call('subscription:check');
            Toastr::success(__('subscription_checked_successfully'));
            return back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }
}
