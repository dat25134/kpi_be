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
            'name' => 'required|string|max:255|unique:roles,name,' . $roleId,
            'code' => 'required|string|max:50|unique:roles,code,' . $roleId,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'nullable|integer|min:1|max:10',
            'status' => 'required|in:active,inactive',
            'guard_name' => 'required|string|max:50',
            'order' => 'nullable|integer',
        ];
    }
} 