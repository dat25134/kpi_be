<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkDescriptionsRequest extends FormRequest
{
    public function authorize()
    {
        // Có thể kiểm tra quyền ở đây nếu muốn, tạm thời cho phép
        return true;
    }

    public function rules()
    {
        return [
            'work_descriptions' => 'required|array|min:1',
            'work_descriptions.*.id' => 'required|integer|exists:work_descriptions,id',
            'work_descriptions.*.quality_weight' => 'nullable|integer',
            'work_descriptions.*.result_level' => 'nullable|integer',
        ];
    }

    public function messages()
    {
        return [
            'work_descriptions.required' => 'Thiếu dữ liệu bảng mô tả công việc.',
            'work_descriptions.array' => 'Dữ liệu bảng mô tả công việc không hợp lệ.',
            'work_descriptions.*.id.required' => 'Thiếu ID của work description.',
            'work_descriptions.*.id.integer' => 'ID work description phải là số nguyên.',
            'work_descriptions.*.id.exists' => 'ID work description không tồn tại.',
            'work_descriptions.*.quality_weight.integer' => 'Trọng số chất lượng phải là số nguyên.',
            'work_descriptions.*.quality_weight.min' => 'Trọng số chất lượng tối thiểu là 1.',
            'work_descriptions.*.quality_weight.max' => 'Trọng số chất lượng tối đa là 5.',
            'work_descriptions.*.result_level.integer' => 'Kết quả thực hiện phải là số nguyên.',
            'work_descriptions.*.result_level.min' => 'Kết quả thực hiện tối thiểu là 1.',
            'work_descriptions.*.result_level.max' => 'Kết quả thực hiện tối đa là 3.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $workDescriptions = $this->input('work_descriptions', []);
            foreach ($workDescriptions as $index => $desc) {
                $workDescId = $desc['id'] ?? null;
                if ($workDescId) {
                    $workDesc = \App\Models\WorkDescription::find($workDescId);
                    if ($workDesc) {
                        $task = $workDesc->task; // Giả sử có quan hệ task
                        $taskName = $task->content ?? 'task';
                        $maxQw = 5; // hoặc lấy từ DB nếu mỗi task có max riêng
                        $minQw = 1;
                        $valQw = $desc['quality_weight'] ?? null;
                        if (!is_null($valQw) && $valQw > $maxQw) {
                            $validator->errors()->add(
                                "work_descriptions.$index.quality_weight",
                                "Trọng số chất lượng của {$taskName} tối đa là $maxQw"
                            );
                        }
                        if (!is_null($valQw) && $valQw < $minQw) {
                            $validator->errors()->add(
                                "work_descriptions.$index.quality_weight",
                                "Trọng số chất lượng của {$taskName} tối thiểu là $minQw"
                            );
                        }
                        $maxRl = 4;
                        $minRl = 1;
                        $valRl = $desc['result_level'] ?? null;
                        if (!is_null($valRl) && $valRl > $maxRl) {
                            $validator->errors()->add(
                                "work_descriptions.$index.result_level",
                                "Kết quả thực hiện của {$taskName} tối đa là $maxRl"
                            );
                        }
                        if (!is_null($valRl) && $valRl < $minRl) {
                            $validator->errors()->add(
                                "work_descriptions.$index.result_level",
                                "Kết quả thực hiện của {$taskName} tối thiểu là $minRl"
                            );
                        }
                    }
                }
            }
        });
    }
} 