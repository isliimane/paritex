<?php

namespace App\Http\Controllers\Admin\Warehouse;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Admin\LanguageInterface;
use App\Repositories\Interfaces\Admin\Warehouse\WarehouseInterface;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WarehouseProduct;
use App\Models\Warehouse;
use App\Http\Requests\CreateWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;

class WarehouseController extends Controller
{
    private $warehouse;
    private $languages;

    public function __construct(WarehouseInterface $warehouse, LanguageInterface $languages)
    {
        $this->warehouse = $warehouse;
        $this->languages = $languages;
    }

    public function index(Request $request)
    {
        $search = $request->search;
        if ($search) {
            $warehouses = $this->warehouse->search($search)->paginate(get_pagination('pagination'));
        } else {
            $warehouses = $this->warehouse->paginate(get_pagination('pagination'));
        }
        $staffs = $this->warehouse->getStaffUsers();
        return view('admin.warehouses.index', compact('warehouses', 'staffs', 'search'));
    }

    public function store(CreateWarehouseRequest $request)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'), __('Demo Mode'));
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $this->warehouse->store($request);
            Toastr::success(__('Warehouse created successfully'), __('Success'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), __('Error'));
            return redirect()->back();
        }
    }

    public function edit(Request $request, $id)
    {
        $languages = $this->languages->all()->orderBy('id', 'asc')->get();
        $lang = $request->lang == '' ? \App::getLocale() : $request->lang;
        $warehouse_lang = $this->warehouse->getByLang($id, $lang);
        $staffs = $this->warehouse->getStaffUsers();
        return view('admin.warehouses.edit', compact('warehouse_lang', 'languages', 'lang', 'staffs'));
    }

    public function update(UpdateWarehouseRequest $request)
    {
        if (config('app.demo_mode')) {
            Toastr::info(__('This function is disabled in demo server.'), __('Demo Mode'));
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $this->warehouse->update($request);
            Toastr::success(__('Warehouse updated successfully'), __('Success'));
            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), __('Error'));
            return redirect()->back();
        }
    }

    public function statusChange(Request $request)
    {
        if (config('app.demo_mode')) {
            $response['message'] = __('This function is disabled in demo server.');
            $response['title'] = __('Demo Mode');
            $response['status'] = 'error';
            return response()->json($response);
        }

        DB::beginTransaction();
        try {
            $this->warehouse->statusChange($request['data']);
            $response['message'] = __('Warehouse status updated successfully');
            $response['title'] = __('Success');
            $response['status'] = 'success';
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Toastr::error($e->getMessage(), __('Error'));
            return redirect()->back();
        }
    }

    /**
     * Get the current total quantity of products stored in a warehouse.
     *
     * @param  \App\Models\Warehouse  $warehouse
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentQuantity(Warehouse $warehouse)
    {
        if (!request()->ajax()) {
            abort(403, 'Direct access not allowed.');
        }

        try {
            $currentQuantity = WarehouseProduct::where('warehouse_id', $warehouse->id)->sum('quantity');
            return response()->json(['current_quantity' => $currentQuantity]);
        } catch (\Exception $e) {
            \Log::error('Error fetching current warehouse quantity: ' . $e->getMessage());
            return response()->json(['error' => __('Failed to fetch current quantity')], 500);
        }
    }
} 