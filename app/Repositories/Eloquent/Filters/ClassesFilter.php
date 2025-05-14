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
            'name' => ['filterLike', Classes::field('name')],
            'courseYear' => ['filterLike', Classes::field('courseYear')],
            'major' => ['filterExact', Classes::field('majorId')]
        ];
    }

}
