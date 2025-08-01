<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'departmentId' => 'nullable|integer|exists:departments,id',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'birthDate' => 'nullable|date|date_format:Y-m-d',
            'gender' => 'nullable|string|in:male,female,other',
            'education' => 'nullable|string',
            'experience' => 'nullable|string',
            'skills' => 'nullable|array',
            'roleName' => 'required|string|exists:roles,name',
            'cccd' => 'required|string|max:20|unique:users,cccd',
            'joinDate' => 'nullable|date|date_format:Y-m-d',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên nhân viên là bắt buộc.',
            'name.string' => 'Tên nhân viên phải là chuỗi ký tự.',
            'name.max' => 'Tên nhân viên không được vượt quá 255 ký tự.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email phải có định dạng hợp lệ.',
            'email.unique' => 'Email đã tồn tại.',
            'phone.string' => 'Số điện thoại phải là chuỗi ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'phone.unique' => 'Số điện thoại đã tồn tại.',
            'departmentId.integer' => 'ID phòng ban phải là số nguyên.',
            'departmentId.exists' => 'Phòng ban không tồn tại.',
            'salary.numeric' => 'Lương phải là số.',
            'salary.min' => 'Lương không được âm.',
            'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
            'birthDate.date' => 'Ngày sinh phải là ngày.',
            'birthDate.date_format' => 'Ngày sinh phải có định dạng Y-m-d.',
            'gender.in' => 'Giới tính phải là male, female hoặc other.',
            'education.string' => 'Học vấn phải là chuỗi ký tự.',
            'experience.string' => 'Kinh nghiệm phải là chuỗi ký tự.',
            'skills.array' => 'Kỹ năng phải là mảng.',
            'roleName.required' => 'Vai trò là bắt buộc.',
            'roleName.string' => 'Vai trò phải là chuỗi ký tự.',
            'roleName.exists' => 'Vai trò không tồn tại.',
            'cccd.required' => 'CCCD là bắt buộc.',
            'cccd.string' => 'CCCD phải là chuỗi ký tự.',
            'cccd.max' => 'CCCD không được vượt quá 20 ký tự.',
            'cccd.unique' => 'CCCD đã tồn tại.',
            'joinDate.date' => 'Ngày tham gia phải là ngày.',
            'joinDate.date_format' => 'Ngày tham gia phải có định dạng Y-m-d.',
        ];
    }
}