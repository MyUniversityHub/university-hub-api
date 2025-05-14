<?php

namespace App\Repositories\Eloquent;

use App\Models\CourseClass;
use App\Repositories\Contracts\CourseClassRepositoryInterface;
use App\Repositories\Eloquent\Filters\CourseClassFilter;

class CourseClassRepositoryImpl extends BaseRepositoryImpl implements CourseClassRepositoryInterface
{
    public function __construct(CourseClass $model, CourseClassFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
