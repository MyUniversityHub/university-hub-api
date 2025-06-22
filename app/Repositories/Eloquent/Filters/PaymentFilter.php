<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\Major;
use App\Models\Payment;
use App\Repositories\Contracts\BaseFilterAbstract;

class PaymentFilter extends BaseFilterAbstract
{

    protected function filters(): array
    {
        return [
            'payment_date'    => ['filterBetweenDatetime', Payment::field('payment_date')],
        ];
    }
}
