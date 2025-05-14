<?php

namespace App\Repositories\Eloquent;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Eloquent\Filters\CourseFilter;

class CourseRepositoryImpl extends BaseRepositoryImpl implements CourseRepositoryInterface
{
    public function __construct(Course $model, CourseFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
