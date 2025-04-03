<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseRequest
{
    public function rulesPut(): array
    {
        return [
            'name' => ['required', 'max:255'],
            'user_name' => ['required', 'max:255', Rule::unique('users', 'email')->ignore($this->route('id'), 'id')],
            'email' => ['required', 'email', 'max:50',  Rule::unique('users', 'user_name')->ignore($this->route('id'), 'id')],
        ];
    }

    public function attributes(): array
    {
        return __('attributes.users');
    }
}
