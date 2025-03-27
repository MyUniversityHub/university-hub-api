<?php

namespace App\Repositories\Eloquent;

use App\Models\Classes;
use App\Repositories\Contracts\ClassesRepositoryInterface;
use App\Repositories\Eloquent\Filters\ClassesFilter;

class ClassesRepositoryImpl extends BaseRepositoryImpl implements ClassesRepositoryInterface
{
    public function __construct(Classes $model, ClassesFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
