<?php

namespace App\Repositories\Contracts;

interface StudentRepositoryInterface extends BaseRepositoryInterface
{
    public function getStudentWithUserInfo($request);
    public function getStudentWithUserInfoById($id);
    public function registerCourse(array $data);
    public function bulkSoftDeleteByUserIds(array $userIds);
}
