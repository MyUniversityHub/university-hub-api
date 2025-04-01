<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\Filters\UserFilter;

class UserRepositoryImpl extends BaseRepositoryImpl implements UserRepositoryInterface
{
    public function __construct(User $model, UserFilter $filter)
    {
        parent::__construct($model, $filter);
    }
}
