<?php

namespace App\Repositories\Contracts;

interface StudentRepositoryInterface extends BaseRepositoryInterface
{
    public function getStudentWithUserInfo();
    public function getStudentWithUserInfoById($id);
    public function registerCourse(array $data);
}
