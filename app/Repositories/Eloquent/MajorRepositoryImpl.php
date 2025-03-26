<?php

namespace App\Repositories\Eloquent;

use App\Models\Major;
use App\Repositories\Contracts\MajorRepositoryInterface;
use App\Repositories\Eloquent\Filters\MajorFilter;

class MajorRepositoryImpl extends BaseRepositoryImpl implements MajorRepositoryInterface
{
    public function __construct(Major $model, MajorFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
