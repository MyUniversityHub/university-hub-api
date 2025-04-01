<?php

namespace App\Http\Requests;

use App\Models\Major;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MajorRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            Major::code() => ['required', 'max:20', 'regex:/^[A-Za-z0-9]+$/', Rule::unique(Major::TABLE_NAME, Major::code())],
            Major::name() => ['required', 'max:120', 'regex:/^[\p{L}\s]+$/u', Rule::unique(Major::TABLE_NAME, Major::name())],
            Major::departmentId() => ['not_in:0']
        ];
    }

    public function rulesPut(): array
    {
        return [
            Major::code() => ['required', 'max:20', 'regex:/^[A-Za-z0-9]+$/', Rule::unique(Major::TABLE_NAME, Major::code())->ignore($this->route('id'), Major::id())],
            Major::name() => ['required', 'max:120', 'regex:/^[\p{L}\s]+$/u', Rule::unique(Major::TABLE_NAME, Major::name())->ignore($this->route('id'), Major::id())],
            Major::departmentId() => ['not_in:0']
        ];
    }

    public function attributes(): array
    {
        return __('attributes.majors');
    }
}
