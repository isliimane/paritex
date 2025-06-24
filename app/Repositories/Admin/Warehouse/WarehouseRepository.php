<?php
namespace App\Repositories\Admin\Warehouse;

use App\Models\Warehouse;
use App\Models\WarehouseLanguage;
use App\Repositories\Interfaces\Admin\Warehouse\WarehouseInterface;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WarehouseRepository implements WarehouseInterface
{
    public function store($request)
    {
        try {
            $warehouse = new Warehouse();
            $warehouse->code = $request->code;
            $warehouse->phone = $request->phone;
            $warehouse->user_id = $request->user_id;
            $warehouse->storage_capacity = $request->storage_capacity;
            $warehouse->status = 1;
            $warehouse->save();

            // $incharge = $warehouse->incharge;
            // $incharge->warehouse_id = $warehouse->id;
            // $incharge->save();

            $request['warehouse_id'] = $warehouse->id;
            if ($request->lang == '') {
                $request['lang'] = 'en';
            }
            $this->langStore($request);
            logStaffActivity('create_warehouse', 'Warehouse', $warehouse->id);

            return true;
        } catch (\Exception $e) {
            Log::error('Warehouse store failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function langStore($request)
    {
        try {
            $lang = new WarehouseLanguage();
            $lang->name = $request->name;
            $lang->address = $request->address;
            $lang->warehouse_id = $request->warehouse_id;
            $lang->lang = $request->lang;
            return $lang->save();
        } catch (\Exception $e) {
            Log::error('Language store failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function get($id)
    {
        return Warehouse::find($id);
    }

    public function all()
    {
        return Warehouse::with('incharge')->latest()->get();
    }

    public function getByLang($id, $lang)
    {
        if($lang == null):
            $warehouseByLang = WarehouseLanguage::with('warehouse')->where('lang', 'en')->where('warehouse_id', $id)->first();
        else:
            $warehouseByLang = WarehouseLanguage::with('warehouse')->where('lang', $lang)->where('warehouse_id', $id)->first();

            if (blank($warehouseByLang)):
                $warehouseByLang = WarehouseLanguage::with('warehouse')->where('lang', 'en')->where('warehouse_id', $id)->first();
                $warehouseByLang['translation_null'] = 'not-found';
            endif;
        endif;
        return $warehouseByLang;
    }

    public function paginate($limit)
    {
        return Warehouse::with('incharge')->latest()->paginate($limit);
    }

    public function update($request)
    {
        $warehouse = $this->get($request->warehouse_id);
        $warehouse->code = $request->code;
        $warehouse->phone = $request->phone;
        $warehouse->user_id = $request->user_id;
        $warehouse->storage_capacity = $request->storage_capacity;
        $warehouse->save();

        if ($request->warehouse_lang_id == '') :
            $this->langStore($request);
        else:
            $this->langUpdate($request);
        endif;
        logStaffActivity('update_warehouse', 'Warehouse', $warehouse->id);
        return true;
    }

    public function langUpdate($request)
    {
        $lang = WarehouseLanguage::find($request->warehouse_lang_id);
        $lang->name = $request->name;
        $lang->address = $request->address;
        $lang->warehouse_id = $request->warehouse_id;
        $lang->lang = $request->lang;
        return $lang->save();
    }

    public function statusChange($request)
    {
        DB::beginTransaction();
        try {
            $warehouse = $this->get($request['id']);
            $warehouse->status = $request['status'];
            $warehouse->save();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function activeWarehouses()
    {
        return Warehouse::where('status', 1)->get();
    }

    public function getStaffUsers()
    {
        return User::where('user_type', 'staff')->get();
    }

    public function search($query)
    {
        return Warehouse::with('incharge')
            ->whereHas('warehouseLanguages', function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%');
            })
            ->orWhere('code', 'like', '%' . $query . '%')
            ->orWhere('phone', 'like', '%' . $query . '%')
            ->latest();
    }
} 