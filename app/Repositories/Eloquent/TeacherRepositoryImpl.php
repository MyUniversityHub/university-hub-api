<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;

class TeacherRepositoryImpl extends BaseRepositoryImpl implements TeacherRepositoryInterface
{
    public function __construct(Teacher $model)
    {
        parent::__construct($model);
    }

    public function getTeacherWithUserInfoById($userId)
    {
        return $this->model->newQuery()
            ->join('users', 'teachers.user_id', '=', 'users.id')
            ->join('departments', 'teachers.department_id', '=', 'departments.department_id')
            ->select('teachers.*', 'users.id as user_id', 'users.email', 'users.name', 'departments.department_id', 'departments.department_name')
            ->where('users.id', $userId)
            ->first();
    }

    public function bulkSoftDeleteByUserIds($userIds)
    {
        return $this->model->whereIn('user_id', $userIds)->delete();
    }

    public function getTeacherWithUserInfo($request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        $query = $this->model->newQuery()
            ->join('users', 'teachers.user_id', '=', 'users.id');

        if($request->has('user_name')) {
            $query->where('users.user_name', 'like', '%' . $request->get('user_name') . '%');
        }

        if($request->has('name')) {
            $query->where('users.name', 'like', '%' . $request->get('name') . '%');
        }

        return $query->orderBy('teachers.teacher_id', 'desc')
                    ->paginate($perPage);
    }


}
