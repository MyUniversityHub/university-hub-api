<?php

namespace App\Repositories\Eloquent;

use App\Models\Statistic;
use App\Repositories\Contracts\StatisticRepositoryInterface;

class StatisticRepositoryImpl extends BaseRepositoryImpl implements StatisticRepositoryInterface
{
    public function __construct(Statistic $model)
    {
        parent::__construct($model, null);
    }
}
