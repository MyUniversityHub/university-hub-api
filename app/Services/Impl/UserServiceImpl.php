<?php

namespace App\Services\Impl;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserServiceInterface;

class UserServiceImpl implements UserServiceInterface
{
    public function __construct(
        public UserRepositoryInterface $userRepository
    )
    {

    }

    public function create(array $data)
    {
        return "OK";
    }


}
