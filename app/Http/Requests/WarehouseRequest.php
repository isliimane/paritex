<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|string|unique:warehouses,code,' . ($this->warehouse_id ?? ''),
            'name' => 'required|max:100',
            'address' => 'required',
            'phone' => 'required',
            'number_of_shelves' => 'required|integer|min:1',
            'columns_per_shelf' => 'required|integer|min:1',
            'storage_capacity' => 'required|integer|min:1',
            'user_id' => 'required|exists:users,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'warehouse_lang_id' => 'nullable|exists:warehouse_languages,id',
            'lang' => 'required|string'
        ];
    }
} 