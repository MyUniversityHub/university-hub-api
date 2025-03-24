<?php

namespace App\Repositories\Eloquent;

use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Repositories\Eloquent\Filters\DepartmentFilter;

class DepartmentRepositoryImpl extends BaseRepositoryImpl implements DepartmentRepositoryInterface
{
    public function __construct(Department $model, DepartmentFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
