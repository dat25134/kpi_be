<?php

namespace App\Http\Requests\TaskProgress;

use Illuminate\Foundation\Http\FormRequest;

class TaskProgressStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'contentProgress' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'contentProgress.required' => 'Nội dung tiến độ không được để trống',
            'contentProgress.string' => 'Nội dung tiến độ phải là chuỗi',
            'contentProgress.max' => 'Nội dung tiến độ không được vượt quá 1000 ký tự',
        ];
    }
} 