<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Course;
use App\Repositories\Contracts\BaseFilterAbstract;

class CourseFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'course_code' => ['filterLike', Course::field('code')],
            'course_name' => ['filterLike', Course::field('name')],
        ];
    }
}
