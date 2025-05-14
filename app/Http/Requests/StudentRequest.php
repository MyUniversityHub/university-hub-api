<?php

namespace App\Http\Requests;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            User::field('name') => ['required', 'max:50', 'regex:/^[\p{L}\s]+$/u'],
//            User::userName() => ['required', 'max:50', Rule::unique(User::TABLE_NAME, User::userName())],
            User::field('email') => ['required', 'max:50', Rule::unique(User::TABLE_NAME, User::email())],
            User::field('password') => ['required', 'max:50'],
            User::field('roleId') => ['required', 'integer'],
            Student::field('classId') => ['required', 'integer']
        ];
    }

    public function rulesPut(): array
    {
        return [
            User::field('name') => ['required', 'max:50', 'regex:/^[\p{L}\s]+$/u'],
//            User::userName() => ['required', 'max:50', Rule::unique(User::TABLE_NAME, User::userName())->ignore($this->route('id'), User::id())],
            User::field('email') => ['required', 'max:50', Rule::unique(User::TABLE_NAME, User::email())->ignore($this->route('id'), User::id())],
            User::field('password') => ['required', 'max:50'],
            User::field('roleId') => ['required', 'integer'],
            Student::field('classId') => ['required', 'integer']
        ];
    }
    public function attributes(): array
    {
        return __('attributes.users');
    }
}
