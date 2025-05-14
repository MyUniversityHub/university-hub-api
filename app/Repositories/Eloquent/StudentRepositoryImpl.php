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

    public function getStudentWithUserInfo()
    {
        return $this->model->newQuery()
            ->join('users', 'students.user_id', '=', 'users.id')
            ->get();
    }

    public function getStudentWithUserInfoById($id)
    {
        return $this->model->newQuery()
            ->join('users', 'students.user_id', '=', 'users.id')
            ->select('students.*', 'users.id as user_id', 'users.email', 'users.name')
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


}
