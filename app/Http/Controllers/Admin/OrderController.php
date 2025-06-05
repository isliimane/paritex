<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\DeliveryHero\DeliveryHeroInterface;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\OrderInterface;
use App\Repositories\Interfaces\UserInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DeliveryHero;
use App\Models\PickupHub;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Repositories\Interfaces\Admin\ShippingInterface;
use App\Models\Order;
use App\Models\DeliveryHistory;
use App\Models\WarehouseProduct;
use App\Models\Warehouse;
use App\Models\ProductStock;
use App\Repositories\Interfaces\Admin\Warehouse\WarehouseInterface;
class OrderController extends Controller
{
    protected $order;
    protected $lang;
    protected $user;
    protected $shipping;
    protected $warehouse;
    public function __construct(OrderInterface $order, LanguageInterface $lang, UserInterface $user, ShippingInterface $shipping, WarehouseInterface $warehouse){
        $this->order    = $order;
        $this->lang     = $lang;
        $this->user     = $user;
        $this->shipping = $shipping;
        $this->warehouse = $warehouse;
    }

    public function index(Request $request){
        try{
            $orders             = $this->order->paginate($request, get_pagination('pagination'));
            return view('admin.orders.orders',compact('orders'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $order = $this->order->updateOrder($request, $id);
            Toastr::success(__('Order updated successfully'));
            return redirect()->route('order.edit', $id);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function edit($id)
    {
        try {
            $order = $this->order->get($id);
            if (!$order) {
                Toastr::error(__('Order not found'));
                return back();
            }
            if($order->payment_status == 'paid'){
                Toastr::error(__('Paid order can not be edited'));
                return back();
            }
            $delivery_heroes = $this->user->allTypeUser()->whereHas('deliveryHero', function ($query) {
                $query->where('status', 1);
                
            })->where('user_type', 'delivery_hero')->where('status', 1)->where('is_user_banned', 0)->get();
            $pickup_hubs = PickupHub::get();
            $countries      = $this->shipping->countries()->where('status', 1)->get();
            $shipping_address = $order->shipping_address;
            $billing_address = $order->billing_address;
            $billing_country = $this->shipping->getCountry($billing_address['address_ids']['country_id']);
            $billing_state = $this->shipping->getState($billing_address['address_ids']['state_id']);
            $billing_city = $this->shipping->getCity($billing_address['address_ids']['city_id']);
            $shipping_country = $this->shipping->getCountry($shipping_address['address_ids']['country_id']);
            $shipping_state = $this->shipping->getState($shipping_address['address_ids']['state_id']);
            $shipping_city = $this->shipping->getCity($shipping_address['address_ids']['city_id']);
            $warehouses = $this->warehouse->all();
            return view('admin.orders.edit', compact('order', 'delivery_heroes', 'pickup_hubs', 'billing_country', 'billing_state', 'billing_city', 'shipping_country', 'shipping_state', 'shipping_city', 'countries', 'warehouses'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }


    public function pickupHubOrder(Request $request){
        try{
            $orders             = $this->order->pickupHubOrder($request, get_pagination('pagination'));
            return view('admin.orders.pickup-hub-orders',compact('orders'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function view($id){
        try{
            $order              = $this->order->get($id);

            $delivery_heroes = $this->user->allTypeUser()->whereHas('deliveryHero', function ($query) {
                $query->where('status', 1);
                
            })->where('user_type', 'delivery_hero')->where('status', 1)->where('is_user_banned', 0)->get();
            $warehouses = $this->warehouse->all();
            return view('admin.orders.order-details', compact('order','delivery_heroes','warehouses'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function invoiceDownload($id)
    {
        try {
            // $order  = $this->order->get($id);
            // $order['font_name'] = $this->order->commonSetting();
            // return view('admin.orders.invoice', compact('order'));

            $this->order->invoiceDownload($id);

            return back();
        } catch (\Exception $e) {

            Toastr::error($e->getMessage());
            return back();
        }
    }

    public function assignDeliveryHero(Request $request){

        DB::beginTransaction();
        try {
            $this->order->assignDeliveryHero($request);
            Toastr::success(__('Delivery Hero Assigned Successfully'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function deliveryStatusChange(Request $request)
    {
        $order = $this->order->get($request['id']);
        if(!$order->warehouse_id):
            Toastr::error(__('No warehouse assigned to this order'));
            return back();
        endif;
        if ($order->delivery_status != 'delivered'):
            if ($order->delivery_status == $request['delivery_status']):
                Toastr::error(__('Delivery status has been already updated to :status', ['status' => $request['delivery_status']]));
                return back();
            else:
                if (($order->payment_status == 'unpaid' || $order->payment_status == 'refunded_to_wallet')  && $request['delivery_status'] == 'delivered'):
                    Toastr::error(__('Unpaid order can not get delivered'));
                    return back();
                else:
                    $status = $this->order->deliveryStatusChange($request);
                    if ($status === 'product_not_available'):
                        Toastr::error(__('Product stock not available'));
                        return redirect()->back();
                    elseif ($status == true):
                        Toastr::success(__('Updated Successfully'));
                        return redirect()->back();
                    else:
                        Toastr::error(__('Something went wrong, Please Check your Email Settings'));
                        return back();
                    endif;
                endif;
            endif;
        else:
            Toastr::error(__('Delivered order can not get updated'));
            return back();
        endif;
    }

    public function paymentStatusChange(Request $request){
        $order = $this->order->get($request['id']);
        if ($order->delivery_status != 'delivered'):
            if ($order->payment_status == 'refunded_to_wallet' && $request['payment_status'] == 'Unpaid'):
                Toastr::error(__('Refunded payment can not get unpaid'));
                return back();
            endif;
            if ($order->payment_status == $request['payment_status']):
                Toastr::error(__('Payment status already been :status', ['status' => $request['payment_status']]));
                return back()->withInput();
            else:
                if ($request['payment_type'] == 'wallet' && $order->user->balance < $order->total_payable):
                    Toastr::error(__('Customer does not have enough wallet balance'));
                    return back();
                endif;

                DB::beginTransaction();
                try {
                    $this->order->paymentStatusChange($request);
                    Toastr::success(__('Updated Successfully'));
                    DB::commit();
                    return back();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Toastr::error($e->getMessage());
                    return redirect()->back();
                }
            endif;
        else:
            Toastr::error(__('Delivered order can not get updated'));
            return back();
            endif;
    }

    public function approveOfflinePayment(Request $request){
        $order = $this->order->get($request['id']);

        $request['payment_status'] = 'offline_payment';

        if ($order->payment_status == $request['payment_status']):

            $response['message'] = __('Payment status already been :status', ['status' => $request['payment_status']]);
            $response['status']  = 'error';
            $response['title']   = __('Opps');
        else:
            DB::beginTransaction();
            try {
                $this->order->paymentStatusChange($request);
                $response['message'] = __('Updated Successfully');
                $response['status']  = 'success';
                $response['title']   = __('Approved');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Toastr::error($e->getMessage());
                return redirect()->back();
            }
        endif;
        return $response;
    }

    public function assignWarehouse(Request $request)
    {
        try {
            DB::beginTransaction();
            $order = $this->order->get($request->id);
            // Validate warehouse selection
            if (!$request->warehouse_id) {
                Toastr::error(__('Please select a warehouse'));
                return back();
            }

            // Check if order is already delivered or canceled
            if ($order->delivery_status == 'delivered' || $order->delivery_status == 'canceled') {
                Toastr::error(__('Cannot change warehouse for delivered or canceled orders'));
                return back();
            }

            // Check if products are available in the selected warehouse
            foreach ($order->orderDetails as $detail) {
                $product_stock = ProductStock::where('product_id', $detail->product_id)
                ->where('name', $detail->variation)
                ->first();
                if(!$product_stock){
                    Toastr::error(__('Product not found in the warehouse'));
                    return back();
                }
                $warehouseStock = WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                    ->where('product_id', $detail->product_id)
                    ->where('product_stock_id', $product_stock->id)
                    ->first();
                if (!$warehouseStock || $warehouseStock->quantity < $detail->quantity) {
                    Toastr::error(__('Insufficient stock in selected warehouse for product: ') . $detail->product->getTranslation('name', \App::getLocale()) . ' (' . $detail->variation . ')');
                    return back();
                }
                $this->order->updateWarehouseStock($detail,true);
            }

            // Update warehouse
            $order->warehouse_id = $request->warehouse_id;
            $order->save();

            // Create order history
            $this->order->deliveryEvent('warehouse_assigned', $order->id, null, 'Warehouse assigned');

            DB::commit();

            Toastr::success(__('Warehouse assigned successfully'));
            return back();

        } catch (\Exception $e) {
            DB::rollback();
            Toastr::error("Something went wrong");
            return back();
        }
    }
}
