<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $roleId = $this->route('id');
        return [
            'code' => 'required|string|max:50|unique:roles,code,' . $roleId,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Mã vị trí là bắt buộc',
            'code.string' => 'Mã vị trí phải là chuỗi',
            'code.max' => 'Mã vị trí không được vượt quá 50 ký tự',
            'code.unique' => 'Mã vị trí đã tồn tại',
            'display_name.required' => 'Tên vị trí là bắt buộc',
            'display_name.string' => 'Tên vị trí phải là chuỗi',
            'display_name.max' => 'Tên vị trí không được vượt quá 255 ký tự',
            'description.string' => 'Mô tả phải là chuỗi',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái phải là active hoặc inactive',
        ];
    }
} 