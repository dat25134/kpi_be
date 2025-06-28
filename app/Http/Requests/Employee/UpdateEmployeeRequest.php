<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('id');
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $userId,
            'position' => 'required|string|in:employee,specialist,manager,director',
            'departmentId' => 'required|integer|exists:departments,id',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string',
            'birthDate' => 'nullable|date_format:d/m/Y',
            'gender' => 'nullable|string|in:male,female,other',
            'education' => 'nullable|string',
            'experience' => 'nullable|string',
            'skills' => 'nullable|array',
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
            'position.required' => 'Chức vụ là bắt buộc.',
            'position.in' => 'Chức vụ phải là employee, specialist, manager hoặc director.',
            'departmentId.required' => 'Phòng ban là bắt buộc.',
            'departmentId.integer' => 'ID phòng ban phải là số nguyên.',
            'departmentId.exists' => 'Phòng ban không tồn tại.',
            'salary.numeric' => 'Lương phải là số.',
            'salary.min' => 'Lương không được âm.',
            'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
            'birthDate.date_format' => 'Ngày sinh phải có định dạng d/m/Y.',
            'gender.in' => 'Giới tính phải là male, female hoặc other.',
            'education.string' => 'Học vấn phải là chuỗi ký tự.',
            'experience.string' => 'Kinh nghiệm phải là chuỗi ký tự.',
            'skills.array' => 'Kỹ năng phải là mảng.',
        ];
    }
} 