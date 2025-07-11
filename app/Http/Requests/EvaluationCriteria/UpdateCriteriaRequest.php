<?php

namespace App\Http\Requests\EvaluationCriteria;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriteriaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_score' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên tiêu chí là bắt buộc',
            'name.max' => 'Tên tiêu chí không được vượt quá 255 ký tự',
            'max_score.required' => 'Điểm tối đa là bắt buộc',
            'max_score.numeric' => 'Điểm tối đa phải là số',
            'max_score.min' => 'Điểm tối đa phải lớn hơn hoặc bằng 0',
            'max_score.max' => 'Điểm tối đa không được vượt quá 100',
        ];
    }
} 