<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\RegistrationFeeDetail;
use App\Repositories\Contracts\BaseFilterAbstract;

class RegistrationFeeDetailFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'status' => ['filterExact', RegistrationFeeDetail::field('status')]
        ];
    }
}
