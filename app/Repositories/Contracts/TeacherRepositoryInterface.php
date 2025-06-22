<?php

namespace App\Repositories\Contracts;

interface TeacherRepositoryInterface extends BaseRepositoryInterface
{
    public function getTeacherWithUserInfo($request);
    public function getTeacherWithUserInfoById($userId);
    public function bulkSoftDeleteByUserIds($userIds);
}
