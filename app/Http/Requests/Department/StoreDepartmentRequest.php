<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên phòng ban là bắt buộc.',
            'name.string' => 'Tên phòng ban phải là chuỗi ký tự.',
            'name.max' => 'Tên phòng ban không được vượt quá 255 ký tự.',
            'code.required' => 'Mã phòng ban là bắt buộc.',
            'code.string' => 'Mã phòng ban phải là chuỗi ký tự.',
            'code.max' => 'Mã phòng ban không được vượt quá 50 ký tự.',
            'code.unique' => 'Mã phòng ban đã tồn tại.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'manager_id.exists' => 'Trưởng phòng không tồn tại.',
            'status.in' => 'Trạng thái phải là active hoặc inactive.',
        ];
    }
} 