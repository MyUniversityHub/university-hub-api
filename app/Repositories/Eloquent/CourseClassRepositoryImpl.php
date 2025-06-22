<?php

namespace App\Repositories\Eloquent;

use App\Models\CourseClass;
use App\Repositories\Contracts\CourseClassRepositoryInterface;
use App\Repositories\Eloquent\Filters\CourseClassFilter;

class CourseClassRepositoryImpl extends BaseRepositoryImpl implements CourseClassRepositoryInterface
{
    public function __construct(CourseClass $model, CourseClassFilter $filter)
    {
        parent::__construct($model, $filter);
    }

    public function getClassesByCourseAndMajor($courseId, $majorId)
    {
        return $this->model->select('course_classes.*', 'courses.course_name', 'users.name as teacher_name')
            ->join('courses', 'courses.course_id', '=', 'course_classes.course_id')
            ->join('teachers', 'teachers.teacher_id', '=', 'course_classes.teacher_id')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('course_classes.course_id', $courseId)
            ->where('course_classes.major_id', $majorId)
            ->where('course_classes.status', COURSE_CLASS_STATUS_OPEN)
            ->orderBy('course_classes.course_class_id', 'desc')
            ->get();
    }

    public function getClassScheduleByTeacherId($teacherId, $startDate, $endDate)
    {
        return $this->model->select('courses.course_name', 'course_classes.course_class_code', 'course_classes.weekdays', 'course_classes.lesson_start', 'course_classes.lesson_end', 'users.name as teacher_name', 'classrooms.room_name')
            ->join('courses', 'courses.course_id', '=', 'course_classes.course_id')
            ->join('teachers', 'teachers.teacher_id', '=', 'course_classes.teacher_id')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->join('classrooms', 'classrooms.classroom_id', '=', 'course_classes.classroom_id')
            ->where('course_classes.teacher_id', $teacherId)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('course_classes.start_date', [$startDate, $endDate])
                    ->orWhereBetween('course_classes.end_date', [$startDate, $endDate])
                    ->orWhere(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->where('course_classes.start_date', '<=', $startDate)
                            ->where('course_classes.end_date', '>=', $endDate);
                    });
            })
            ->get();
    }


}
