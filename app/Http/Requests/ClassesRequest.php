<?php

namespace App\Http\Requests;

use App\Models\Classes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassesRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            Classes::name() => ['required', 'max:120', 'regex:/^[A-Za-z0-9]+$/', Rule::unique(Classes::TABLE_NAME, Classes::name())],
            Classes::courseYear() => ['required', 'max:50'],
            Classes::advisorName() => ['required', 'max:50', 'regex:/^[\p{L}\s]+$/u'],
        ];
    }

    public function rulesPut(): array
    {
        return [
            Classes::name() => ['required', 'max:120', 'regex:/^[A-Za-z0-9]+$/', Rule::unique(Classes::TABLE_NAME, Classes::name())->ignore($this->route('id'), Classes::id())],
            Classes::courseYear() => ['required', 'max:50'],
            Classes::advisorName() => ['required', 'max:50', 'regex:/^[\p{L}\s]+$/u'],
        ];
    }

    public function attributes(): array
    {
        return __('attributes.classes');
    }
}
