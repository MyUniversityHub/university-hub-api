<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'user_name' => ['required', 'max:255', Rule::unique('users', 'email')],
            'email' => ['email',  Rule::unique('users', 'user_name')],
            'role_id' => ['required', 'integer'],
            'class_id' => ['nullable', 'required_if:role_id,2', 'exists:classes,class_id'],
            'department_id' => ['nullable', 'required_if:role_id,3', 'exists:departments,department_id'],
        ];
    }

    public function rulesPut(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'user_name' => ['required', 'max:255', Rule::unique('users', 'email')->ignore($this->route('id'), 'id')],
            'email' => ['email', 'max:50',  Rule::unique('users', 'user_name')->ignore($this->route('id'), 'id')],
            'role_id' => ['required', 'integer'],
            'class_id' => ['nullable', 'required_if:role_id,2', 'exists:classes,class_id'],
            'department_id' => ['nullable', 'required_if:role_id,3', 'exists:departments,department_id'],
        ];
    }

    public function messages()
    {
        return [
            'class_id.required_if' => 'Lớp học là bắt buộc khi vai trò là Học sinh.',
            'class_id.exists' => 'Lớp học không tồn tại trong hệ thống.',
            'department_id.required_if' => 'Khoa là bắt buộc khi vai trò là Giảng viên',
            'department_id.exists' => 'Khoa không tồn tại trong hệ thống.',
        ];
    }

    public function attributes(): array
    {
        return __('attributes.users');
    }
}
