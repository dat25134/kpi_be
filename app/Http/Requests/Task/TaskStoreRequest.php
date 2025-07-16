<?php

namespace App\Http\Requests\Task;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    public function authorize()
    {
        // Adjust authorization logic as needed
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|string',
            'startDate' => 'required|date',
            'deadline' => 'required|date|after:startDate|bail',
            'count' => 'required|integer|min:1|max:5|bail',
            'assigner' => 'required|exists:users,id|bail',
            'mainHandler' => 'required|exists:users,id|bail',
            'assignees' => 'nullable|array|bail',
            'assignees.*' => 'exists:users,id|bail',
            'category' => 'required|exists:categories,id|bail',
            'department' => 'nullable|exists:departments,id|bail',
            'files' => 'nullable|array',
            'files.*' => 'file|max:10240', // 10MB mỗi file
            'parent_id' => 'nullable|exists:tasks,id',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');
            if ($parentId) {
                $parent = \App\Models\Task::find($parentId);
                if ($parent) {
                    $startDate = $this->input('startDate');
                    $deadline = $this->input('deadline');
                    if ($startDate && ($startDate < $parent->start_date)) {
                        $validator->errors()->add('startDate', 'Ngày bắt đầu của task con không được nhỏ hơn ngày bắt đầu của task cha ('. Carbon::parse($parent->start_date)->format('d/m/Y') .')');
                    }
                    if ($deadline && ($deadline > $parent->due_date)) {
                        $validator->errors()->add('deadline', 'Hạn xử lý của task con không được lớn hơn hạn xử lý của task cha ('. Carbon::parse($parent->due_date)->format('d/m/Y') .')');
                    }
                }
            }
        });
    }

    public function messages()
    {
        return [
            'content.required' => 'Nội dung không được để trống',
            'content.string' => 'Nội dung không hợp lệ',
            'startDate.required' => 'Ngày bắt đầu không được để trống',
            'startDate.date' => 'Ngày bắt đầu không hợp lệ',
            'deadline.required' => 'Hạn xử lý không được để trống',
            'deadline.date' => 'Hạn xử lý không hợp lệ',
            'deadline.after' => 'Hạn xử lý phải sau ngày bắt đầu',
            'count.required' => 'Trọng số không được để trống',
            'count.integer' => 'Số lượng không hợp lệ',
            'count.min' => 'Số lượng phải lớn hơn 0',
            'count.max' => 'Số lượng phải nhỏ hơn 5',
            'assigner.required' => 'Người tạo không được để trống',
            'assigner.exists' => 'Người tạo không hợp lệ',
            'mainHandler.required' => 'Người chịu trách nhiệm không được để trống',
            'mainHandler.exists' => 'Người chịu trách nhiệm không hợp lệ',
            'assignees.array' => 'Danh sách người tham gia không hợp lệ',
            'assignees.exists' => 'Danh sách người tham gia không hợp lệ',
            'category.required' => 'Danh mục không được để trống',
            'category.exists' => 'Danh mục không hợp lệ',
            'department.exists' => 'Phòng ban không hợp lệ',
            'files.array' => 'Danh sách file không hợp lệ',
            'files.*.file' => 'File không hợp lệ',
            'files.*.max' => 'File không được lớn hơn 10MB',
            'parent_id.exists' => 'Task cha không hợp lệ',
        ];
    }
} 