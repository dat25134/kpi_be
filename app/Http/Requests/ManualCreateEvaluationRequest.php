<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManualCreateEvaluationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000|max:2100',
        ];
    }

    public function messages()
    {
        return [
            'month.required' => 'Vui lòng chọn tháng.',
            'month.integer' => 'Tháng phải là số nguyên.',
            'month.min' => 'Tháng không hợp lệ.',
            'month.max' => 'Tháng không hợp lệ.',
            'year.required' => 'Vui lòng chọn năm.',
            'year.integer' => 'Năm phải là số nguyên.',
            'year.min' => 'Năm không hợp lệ.',
            'year.max' => 'Năm không hợp lệ.',
        ];
    }
} 