<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\CurriculumProgram;
use App\Repositories\Contracts\BaseFilterAbstract;

class CurriculumProgramFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'semester' => ['filterLike', CurriculumProgram::field('semester')],
        ];
    }
}
