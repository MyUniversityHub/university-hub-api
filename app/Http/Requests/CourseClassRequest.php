<?php

namespace App\Http\Requests;

use App\Models\Course;
use App\Models\CourseClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseClassRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            CourseClass::field('course_id') => ['required'],
            CourseClass::field('classroom_id') => ['nullable'],
            CourseClass::field('teacher_id') => ['nullable'],
            CourseClass::field('weekdays') => ['required'],
            CourseClass::field('semester') => ['required', 'integer', 'min:1'],
            CourseClass::field('lesson_start') => ['required', 'integer', 'min:1'],
            CourseClass::field('lesson_end') => ['required', 'integer', 'min:1'],
            CourseClass::field('start_date') => ['required', 'date'],
            CourseClass::field('end_date') => ['required', 'date', 'after_or_equal:start_date']
        ];
    }

    public function rulesPut(): array
    {
        return [
            CourseClass::field('course_id') => ['required'],
            CourseClass::field('classroom_id') => ['nullable'],
            CourseClass::field('teacher_id') => ['nullable'],
            CourseClass::field('weekdays') => ['required'],
            CourseClass::field('semester') => ['required', 'integer', 'min:1'],
            CourseClass::field('lesson_start') => ['required', 'integer', 'min:1'],
            CourseClass::field('lesson_end') => ['required', 'integer', 'min:1'],
            CourseClass::field('start_date') => ['required', 'date'],
            CourseClass::field('end_date') => ['required', 'date', 'after_or_equal:start_date']
        ];
    }

    public function attributes(): array
    {
        return __('attributes.course_classes');
    }
}
