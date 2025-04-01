<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Major;
use App\Repositories\Contracts\BaseFilterAbstract;

class MajorFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'code' => ['filterLike', Major::code()],
            'name' => ['filterLike', Major::name()],
            'department' => ['filterExact', Major::departmentId()]
        ];
    }

}
