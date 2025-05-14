<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\CourseClass;
use App\Repositories\Contracts\BaseFilterAbstract;

class CourseClassFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'code' => ['filterLike', CourseClass::field('course_class_code')],
            'start_date' => ['filterDate', CourseClass::field('start_date')],
            'end_date' => ['filterDate', CourseClass::field('end_date')],
        ];
    }
}
