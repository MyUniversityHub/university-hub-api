<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            Course::field('name') => ['required', 'max:120', 'regex:/^[\pL\pN\s\-]+$/u', Rule::unique(Course::TABLE_NAME, Course::field('name'))],
            Course::field('creditHours') => ['required', 'integer', 'min:1'],
        ];
    }

    public function rulesPut(): array
    {
        return [
            Course::field('name') => ['required', 'max:120', 'regex:/^[\p{L}\s\-]+$/u', Rule::unique(Course::TABLE_NAME, Course::field('name'))->ignore($this->route('id'), Course::field('id'))],
            Course::field('creditHours') => ['required', 'integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return __('attributes.courses');
    }
}

