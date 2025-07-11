<?php

namespace App\Http\Requests\EvaluationCriteria;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryCriteriaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên danh mục không được để trống',
            'name.string' => 'Tên danh mục không hợp lệ',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
        ];
    }
} 