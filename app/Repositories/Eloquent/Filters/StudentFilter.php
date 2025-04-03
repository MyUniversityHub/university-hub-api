<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Student;
use App\Repositories\Contracts\BaseFilterAbstract;

class StudentFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'code' => ['filterLike', Student::code()]
        ];
    }

}
