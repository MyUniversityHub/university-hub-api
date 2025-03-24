<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Department;
use App\Repositories\Contracts\BaseFilterAbstract;

class DepartmentFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'code' => ['filterLike', Department::code()],
            'name' => ['filterLike', Department::name()]
        ];
    }

}
