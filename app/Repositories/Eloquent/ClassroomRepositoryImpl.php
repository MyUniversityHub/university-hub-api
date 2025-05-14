<?php

namespace App\Repositories\Eloquent;

use App\Models\Classroom;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Eloquent\Filters\ClassroomFilter;

class ClassroomRepositoryImpl extends BaseRepositoryImpl implements ClassroomRepositoryInterface
{
    public function __construct(Classroom $model, ClassroomFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
