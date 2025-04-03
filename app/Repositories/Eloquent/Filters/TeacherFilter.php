<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Teacher;
use App\Repositories\Contracts\BaseFilterAbstract;

class TeacherFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'code' => ['filterLike', Teacher::code()]
        ];
    }

}
