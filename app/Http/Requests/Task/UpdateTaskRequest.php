<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize()
    {
        // Điều chỉnh logic phân quyền nếu cần
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'sometimes|required|string',
            'startDate' => 'sometimes|required|date',
            'deadline' => 'sometimes|required|date',
            'category' => 'sometimes|required|exists:categories,id',
            'department' => 'nullable|exists:departments,id',
            'count' => 'nullable|integer|min:1|max:10',
            'assigner' => 'sometimes|required|exists:users,id',
            'mainHandler' => 'sometimes|required|exists:users,id',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'assignees' => 'nullable|array',
            'assignees.*' => 'exists:users,id',
        ];
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
            'category.required' => 'Danh mục không được để trống',   
            'category.exists' => 'Danh mục không hợp lệ',
            'department.exists' => 'Phòng ban không hợp lệ',
            'count.integer' => 'Số lượng không hợp lệ',
            'count.min' => 'Số lượng phải lớn hơn 0',
            'count.max' => 'Số lượng phải nhỏ hơn 5',
            'assigner.required' => 'Người tạo không được để trống',
            'assigner.exists' => 'Người tạo không hợp lệ',
            'mainHandler.required' => 'Người chịu trách nhiệm không được để trống',
            'mainHandler.exists' => 'Người chịu trách nhiệm không hợp lệ',
            'status.in' => 'Trạng thái không hợp lệ',
            'assignees.array' => 'Danh sách người tham gia không hợp lệ',
            'assignees.*.exists' => 'Người tham gia không hợp lệ',
        ];
    }
} 