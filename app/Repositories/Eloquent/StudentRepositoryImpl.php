<?php

namespace App\Repositories\Eloquent;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Eloquent\Filters\StudentFilter;

class StudentRepositoryImpl extends BaseRepositoryImpl implements StudentRepositoryInterface
{
    public function __construct(Student $model, StudentFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
