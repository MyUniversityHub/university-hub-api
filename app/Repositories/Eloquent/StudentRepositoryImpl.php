<?php

namespace App\Repositories\Eloquent;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Eloquent\Filters\StudentFilter;
use Illuminate\Support\Facades\DB;

class StudentRepositoryImpl extends BaseRepositoryImpl implements StudentRepositoryInterface
{
    public function __construct(Student $model, StudentFilter $filter)
    {
        parent::__construct($model, $filter);
    }

    public function getStudentWithUserInfo($request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        $query = $this->model->newQuery()
            ->select('students.admission_year', 'students.student_id', 'students.class_id', 'users.*', 'majors.major_id', 'departments.department_id')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('classes', 'students.class_id', '=', 'classes.class_id')
            ->join('majors', 'classes.major_id', '=', 'majors.major_id')
            ->join('departments', 'majors.department_id', '=', 'departments.department_id');
        if ($request->has('user_name')) {
            $query->where('users.user_name', 'like', '%' . $request->get('user_name') . '%');
        }
        if ($request->has('name')) {
            $query->where('users.name', 'like', '%' . $request->get('name') . '%');
        }

        return $query->paginate($perPage);
    }

    public function getStudentWithUserInfoById($id)
    {
        return $this->model->newQuery()
            ->join('users', 'students.user_id', '=', 'users.id')
            ->join('classes', 'students.class_id', '=', 'classes.class_id')
            ->join('majors', 'classes.major_id', '=', 'majors.major_id')
            ->join('departments', 'majors.department_id', '=', 'departments.department_id')
            ->select('students.*', 'users.id as user_id', 'users.email', 'users.name', 'majors.major_id', 'majors.major_name', 'departments.department_id', 'departments.department_name')
            ->where('users.id', $id)
            ->first();
    }

    public function registerCourse(array $data)
    {
        // Check if the course_class_id exists in the course_classes table
        $courseClassExists = DB::table('course_classes')
            ->where('course_class_id', $data['course_class_id'])
            ->exists();
        if (!$courseClassExists) {
            throw new \Exception('The specified course class does not exist.');
        }

        // Insert the registration record
        return DB::table('student_course_registrations')->insert($data);
    }

    public function bulkSoftDeleteByUserIds(array $userIds)
    {
        return $this->model->whereIn('user_id', $userIds)->delete();
    }


}
