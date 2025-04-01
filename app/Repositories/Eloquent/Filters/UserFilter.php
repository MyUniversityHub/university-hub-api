<?php

namespace App\Repositories\Eloquent\Filters;

use App\Models\User;
use App\Repositories\Contracts\BaseFilterAbstract;

class UserFilter extends BaseFilterAbstract
{
    protected function filters(): array
    {
        return [
            'name' => ['filterLike', 'name'],
            'user_name' => ['filterLike', 'user_name']
        ];
    }

}
