<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'max:255'],
            'user_name' => ['required', 'max:255', Rule::unique('users', 'email')->ignore($this->route('id'), 'id')],
            'email' => ['email',  Rule::unique('users', 'user_name')->ignore($this->route('id'), 'id')],
            'password' => ['required', 'min:8'],
            'role_id' => ['required', 'integer'],
            'class_id' => ['nullable', 'required_if:role_id,2', 'exists:classes,id'],
            'department_id' => ['nullable', 'required_if:role_id,3', 'exists:departments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên không được để trống.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'user_name.required' => 'Tên đăng nhập không được để trống.',
            'user_name.max' => 'Tên đăng nhập không được vượt quá 255 ký tự.',
            'user_name.unique' => 'Tên đăng nhập đã tồn tại trong hệ thống.',
            //'email.required' => 'Email không được để trống.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            //'email.unique' => 'Email đã tồn tại trong hệ thống.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'role_id.required' => 'Vai trò không được để trống.',
            'role_id.integer' => 'Vai trò phải là chữ số.',
            'role_id.exists' => 'Vai trò không tồn tại trong hệ thống.',
            'class_id.required_if' => 'Lớp học là bắt buộc khi vai trò là Học sinh.',
            'class_id.exists' => 'Lớp học không tồn tại trong hệ thống.',
            'department_id.required_if' => 'Khoa là bắt buộc khi vai trò là Giảng viên',
            'department_id.exists' => 'Khoa không tồn tại trong hệ thống.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Dữ liệu không hợp lệ!',
            'errors' => $validator->errors()
        ], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
