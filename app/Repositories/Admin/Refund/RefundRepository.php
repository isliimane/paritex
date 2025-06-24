<?php

namespace App\Repositories\Admin\Refund;

use App\Events\PusherNotification;
use App\Models\User;
use App\Models\Order;
use App\Models\Refund;
use App\Models\Wallet;
use App\Models\OrderDetail;
use App\Repositories\Admin\Addon\WalletRepository;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Traits\PaymentTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use http\Env\Request;
use Illuminate\Support\Facades\DB;
use Sentinel;
use App\Repositories\Interfaces\Admin\Refund\RefundInterface;

class RefundRepository implements RefundInterface
{
    use PaymentTrait;
    protected $order;
    protected $wallet;

    public function __construct(OrderInterface $order, WalletRepository $wallet)
    {
        $this->order    = $order;
        $this->wallet   = $wallet;
    }

    public function get($id)
    {
        return Refund::find($id);
    }

    public function all()
    {
        return Refund::with('user', 'order', 'seller')->latest();
    }

    public function paginate($request, $limit, $refund_for = '')
    {
        $refund_for     = $refund_for != '' ? $refund_for : ($request->has('s') ? $request->s : '');
        return $this->all()->when($refund_for != '', function ($q) use ($refund_for) {
                    $q->where('status', $refund_for);
                })
                ->where(function($q) use ($request) {
                    $q->whereHas('order', function ($q) use ($request) {
                        $q->where('code', 'like', '%' . $request->q . '%');
                    })->orWhereHas('orderDetail.product.productLanguages', function ($qu) use ($request) {
                        $qu->where('name', 'like', '%' . $request->q . '%');
                    });
                })
                ->paginate($limit);
    }

    public function approvedRefund($id)
    {
        DB::beginTransaction();
        try {
            $refund      = Refund::find($id);

            if (authUser()->user_type == 'admin' || authUser()->user_type == 'staff'):
                $refund->admin_approval = 'approved';
            endif;
            if ($refund->admin_approval == 'approved'):
                $refund->status          = 'approved';
            endif;
            $refund->save();
            logStaffActivity('approve_refund', 'Refund', $refund->id);

            sendNotification($refund->user,"Your refund request for {$refund->orderDetail->product->product_name} is approved.",'success',"get-invoice/{$refund->order->code}",'');

            DB::commit();
            Toastr::success(__('Refund successfully approved'));
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error(__($e->getMessage()));
            return false;
        }
    }

    public function payNow($id)
    {
        DB::beginTransaction();
        try {
            $refund = Refund::find($id);
            $order  = $refund->order;
            $payment_details = [];
            $payment_type = '';

            if ($refund->status == 'approved'):
                $data['user_id']    = 1;
                $data['order_id']   = $refund->order_id;
                $data['amount']     = $refund->refund_amount;
                $data['source']     = 'order_refunded';
                $data['type']       = 'expense';
                $data['status']     = 'approved';
                $payment_type = 'system_automated';
                $data['payment_method']     = 'system_automated';
                $data['payment_details']    = ['type'=> 'system_automated'];
                $this->wallet->adminBalanceRemove($data, 'order_refunded');

                $data['user_id']    = $refund->user_id;
                $data['order_id']   = $refund->order_id;
                $data['amount']     = $refund->refund_amount;
                $data['source']     = 'order_refunded';
                $data['type']       = 'income';
                $data['status']     = 'approved';
                $this->wallet->customerBalanceStore($data, 'order_refunded');
            endif;
            $refund->status             = 'processed';
            $refund->payment_type       = $payment_type;
            $refund->payment_details    = $payment_details;
            $refund->processed_by       = authId();
            $refund->save();
            logStaffActivity('pay_refund', 'Refund', $refund->id);
            $this->order->updateQuantity($refund->orderDetail, false,'refund');
            $this->order->saleUpdate($refund->orderDetail, true);

            sendNotification($refund->user,
                "Your refund request for {$refund->orderDetail->product->product_name} is processed. Refunded amount is add to your wallet.",
                'success',"get-invoice/{$refund->order->code}",'');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function wallet($refund, $seller, $admin_account_details)
    {

        $wallet                     = new Wallet;
        $wallet->user_id            = $refund->user_id;
        $wallet->amount             = $refund->refund_amount;
        $wallet->source             = 'refund';
        $wallet->type               = 'income';
        $wallet->payment_method     = $admin_account_details['account_type'];
        $wallet->payment_details    = $admin_account_details;
        $wallet->save();
        $user                       = User::findOrFail($refund->user_id);

        $user->balance += $refund->refund_amount;
        $user->save();

        $wallet                     = new Wallet;
        $wallet->user_id            = $seller->id;
        $wallet->amount             = $refund->refund_amount;
        $wallet->source             = 'refund';
        $wallet->type               = 'expense';
        $wallet->payment_method     = $admin_account_details['account_type'];
        $wallet->payment_details    = $admin_account_details;
        $wallet->save();

        return true;
    }

    public function rejectRefund($request)
    {
        DB::beginTransaction();
        try {
            $refund = Refund::find($request->id);
            $refund->reject_reason = $request->reject_reason;
            $refund->admin_approval = 'rejected';
            $refund->status = 'rejected';
            $refund->save();
            logStaffActivity('reject_refund', 'Refund', $refund->id);

            sendNotification($refund->user,"Your refund request for {$refund->orderDetail->product->product_name} is rejected.",'warning',"get-invoice/{$refund->order->code}",'');

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function store($request)
    {
        $order_detail                   = $this->order->getDetail($request->order_detail_id);
        $refund                         = new Refund();
        $refund->user_id                = Sentinel::getUser()->id;
        $refund->order_id               = $order_detail->order->id;
        $refund->order_detail_id        = $order_detail->id;
        $refund->seller_id              = $order_detail->order->seller_id;

        $refund->shipping_cost          = $order_detail->shipping_cost['total_cost'];
        $refund->total_amount           = (($order_detail->price * $order_detail->quantity) + $order_detail->tax + $order_detail->shipping_cost['total_cost']) - ($order_detail->discount + $order_detail->coupon_discount['discount']);

        if (settingHelper('refund_with_shipping_cost') != '' && settingHelper('refund_with_shipping_cost') == 1):
            $refund->refund_amount      = $refund->total_amount;
        else:
            $refund->refund_amount      = $refund->total_amount - $refund->shipping_cost;
        endif;
        $refund->remark                 = $request->refund_reason;
        $refund->save();

        sendNotification($refund->seller,"New refund request for {$order_detail->product->product_name}.",'success',"refunds",'');

    }
}
