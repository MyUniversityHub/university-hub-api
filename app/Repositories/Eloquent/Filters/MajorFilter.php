<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Major;
use App\Repositories\Contracts\BaseFilterAbstract;

class MajorFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'code' => ['filterLike', Major::field('code')],
            'name' => ['filterLike', Major::field('name')],
            'department' => ['filterExact', Major::field('departmentId')]
        ];
    }

}
