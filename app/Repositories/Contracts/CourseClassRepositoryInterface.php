<?php

namespace App\Repositories\Contracts;

interface CourseClassRepositoryInterface extends BaseRepositoryInterface
{
    public function getClassesByCourseAndMajor($courseId, $majorId);

    public function getClassScheduleByTeacherId($teacherId, $startDate, $endDate);
}
