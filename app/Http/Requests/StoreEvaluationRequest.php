<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $status = $this->input('status', 'draft');
        
        $rules = [
            'status' => ['sometimes', Rule::in(['draft', 'submitted', 'level1_approved', 'level2_approved', 'completed'])],
            'total_score' => 'sometimes|numeric|min:0|max:100',
            'final_grade' => ['sometimes', Rule::in(['A', 'B', 'C', 'D'])],
        ];

        // Validation cho evaluation_details theo giai đoạn
        if ($this->has('evaluation_details')) {
            $rules['evaluation_details'] = 'array';
            $rules['evaluation_details.*.criteria_id'] = 'required|exists:evaluation_criteria,id';
            
            switch ($status) {
                case 'draft':
                case 'submitted':
                    $rules['evaluation_details.*.self_score'] = 'sometimes|numeric|min:0';
                    $rules['evaluation_details.*.self_comment'] = 'sometimes|string';
                    break;
                    
                case 'level1_approved':
                    $rules['evaluation_details.*.level1_score'] = 'sometimes|numeric|min:0';
                    $rules['evaluation_details.*.level1_comment'] = 'sometimes|string';
                    break;
                    
                case 'level2_approved':
                    $rules['evaluation_details.*.level2_score'] = 'sometimes|numeric|min:0';
                    $rules['evaluation_details.*.level2_comment'] = 'sometimes|string';
                    break;
                    
                default:
                    // Các giai đoạn khác không cho phép thay đổi evaluation details
                    $rules['evaluation_details'] = 'prohibited';
            }
        }

        // Validation cho work_descriptions (chỉ cấp 1 mới có quyền)
        // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
        /*
        if ($this->has('work_descriptions')) {
            // Kiểm tra quyền cập nhật work descriptions
            if (!$this->canUpdateWorkDescriptions()) {
                throw new \InvalidArgumentException('Bạn không có quyền cập nhật work descriptions. Chỉ trưởng phòng mới có quyền này.');
            }
            
            $rules['work_descriptions'] = 'array';
            $rules['work_descriptions.*.id'] = 'required|exists:work_descriptions,id';
            $rules['work_descriptions.*.result_level'] = 'sometimes|integer|min:1|max:4';
            $rules['work_descriptions.*.quality_weight'] = 'sometimes|integer|min:1|max:5';
        }
        */

        return $rules;
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateScoresNotExceedMax($validator);
        });
    }

    /**
     * Validate điểm số không vượt quá max_score của tiêu chí
     */
    private function validateScoresNotExceedMax($validator)
    {
        $evaluationDetails = $this->input('evaluation_details', []);
        
        foreach ($evaluationDetails as $index => $detail) {
            $criteriaId = $detail['criteria_id'] ?? null;
            if (!$criteriaId) {
                continue;
            }

            // Lấy max_score của tiêu chí
            $criteria = \App\Models\EvaluationCriteria::find($criteriaId);
            if (!$criteria) {
                $validator->errors()->add(
                    "evaluation_details.{$index}.criteria_id",
                    'Tiêu chí đánh giá không tồn tại.'
                );
                continue;
            }

            // Kiểm tra self_score
            if (isset($detail['self_score']) && $detail['self_score'] > $criteria->max_score) {
                $validator->errors()->add(
                    "evaluation_details.{$index}.self_score",
                    "Điểm tự đánh giá ({$criteria->name}) không được vượt quá điểm tối đa của tiêu chí ({$criteria->max_score})."
                );
            }

            // Kiểm tra level1_score
            if (isset($detail['level1_score']) && $detail['level1_score'] > $criteria->max_score) {
                $validator->errors()->add(
                    "evaluation_details.{$index}.level1_score",
                    "Điểm đánh giá cấp 1 ({$criteria->name}) không được vượt quá điểm tối đa của tiêu chí ({$criteria->max_score})."
                );
            }

            // Kiểm tra level2_score
            if (isset($detail['level2_score']) && $detail['level2_score'] > $criteria->max_score) {
                $validator->errors()->add(
                    "evaluation_details.{$index}.level2_score",
                    "Điểm đánh giá cấp 2 ({$criteria->name}) không được vượt quá điểm tối đa của tiêu chí ({$criteria->max_score})."
                );
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Trạng thái không hợp lệ. Các giá trị cho phép: draft, submitted, level1_approved, level2_approved, completed',
            'total_score.numeric' => 'Tổng điểm phải là số',
            'total_score.min' => 'Tổng điểm không được nhỏ hơn 0',
            'total_score.max' => 'Tổng điểm không được lớn hơn 100',
            'final_grade.in' => 'Xếp loại không hợp lệ. Các giá trị cho phép: A, B, C, D',
            'evaluation_details.array' => 'Chi tiết đánh giá phải là mảng',
            'evaluation_details.*.criteria_id.required' => 'ID tiêu chí đánh giá là bắt buộc',
            'evaluation_details.*.criteria_id.exists' => 'Tiêu chí đánh giá không tồn tại',
            'evaluation_details.*.self_score.numeric' => 'Điểm tự đánh giá phải là số',
            'evaluation_details.*.self_score.min' => 'Điểm tự đánh giá không được nhỏ hơn 0',
            'evaluation_details.*.level1_score.numeric' => 'Điểm đánh giá cấp 1 phải là số',
            'evaluation_details.*.level1_score.min' => 'Điểm đánh giá cấp 1 không được nhỏ hơn 0',
            'evaluation_details.*.level2_score.numeric' => 'Điểm đánh giá cấp 2 phải là số',
            'evaluation_details.*.level2_score.min' => 'Điểm đánh giá cấp 2 không được nhỏ hơn 0',
            'evaluation_details.prohibited' => 'Không thể thay đổi chi tiết đánh giá ở giai đoạn này',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'status' => 'trạng thái',
            'total_score' => 'tổng điểm',
            'final_grade' => 'xếp loại',
            'evaluation_details' => 'chi tiết đánh giá',
            'evaluation_details.*.criteria_id' => 'tiêu chí đánh giá',
            'evaluation_details.*.self_score' => 'điểm tự đánh giá',
            'evaluation_details.*.self_comment' => 'nhận xét tự đánh giá',
            'evaluation_details.*.level1_score' => 'điểm đánh giá cấp 1',
            'evaluation_details.*.level1_comment' => 'nhận xét đánh giá cấp 1',
            'evaluation_details.*.level2_score' => 'điểm đánh giá cấp 2',
            'evaluation_details.*.level2_comment' => 'nhận xét đánh giá cấp 2',
        ];
    }

    /**
     * Kiểm tra quyền cập nhật work descriptions
     */
    // TODO: Tạm thời comment để tập trung vào chức năng đánh giá theo từng cấp
    /*
    private function canUpdateWorkDescriptions(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Chỉ cấp 1 (trưởng phòng) mới có quyền cập nhật work descriptions
        $userRole = $user->roles->first();
        return $userRole && $userRole->name === 'truongphong';
    }
    */
} 