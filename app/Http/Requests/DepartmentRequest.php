<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            Department::code() => ['required', 'max:20', 'regex:/^[A-Za-z0-9]+$/', Rule::unique(Department::TABLE_NAME, Department::code())],
            Department::name() => ['required', 'max:120', 'regex:/^[\p{L}\s]+$/u', Rule::unique(Department::TABLE_NAME, Department::name())],
            Department::description() => ['required']
        ];
    }

    public function rulesPut(): array
    {
        return [
            Department::code() => ['required', 'max:20', 'regex:/^[A-Za-z0-9]+$/', Rule::unique(Department::TABLE_NAME, Department::code())->ignore($this->route('id'), Department::id())],
            Department::name() => ['required', 'max:120', 'regex:/^[\p{L}\s]+$/u', Rule::unique(Department::TABLE_NAME, Department::name())->ignore($this->route('id'), Department::id())],
            Department::description() => ['required']
        ];
    }

    public function attributes(): array
    {
        return __('attributes.departments');
    }
}
