<?php

namespace App\Repositories\Eloquent\Filters;

use App\Http\Controllers\ClassesController;
use App\Models\Classes;
use App\Repositories\Contracts\BaseFilterAbstract;

class ClassesFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'name' => ['filterLike', Classes::name()],
            'courseYear' => ['filterLike', Classes::courseYear()],
            'major' => ['filterExact', Classes::majorId()]
        ];
    }

}
