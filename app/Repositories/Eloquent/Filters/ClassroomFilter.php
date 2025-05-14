<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Classroom;
use App\Repositories\Contracts\BaseFilterAbstract;

class ClassroomFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'room_name' => ['filterLike', Classroom::field('name')],
        ];
    }
}
