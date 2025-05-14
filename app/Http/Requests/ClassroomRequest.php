<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassroomRequest extends BaseRequest
{
    public function rulesPost(): array
    {
        return [
            Classroom::field('name') => ['required', 'max:120', 'unique:classrooms,room_name'],
        ];
    }

    public function rulesPut(): array
    {
        return [
            Classroom::field('name') => ['required', 'max:120', 'unique:classrooms,room_name,' . $this->route('id') . ',classroom_id'],
        ];
    }

    public function attributes(): array
    {
        return __('attributes.classrooms');
    }
}
