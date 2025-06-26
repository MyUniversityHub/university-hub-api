<?php

namespace App\Repositories\Contracts;

interface StudentCourseResultRepositoryInterface extends BaseRepositoryInterface
{
    public function updateCompositeKey(array $keys, array $data);

    public function deleteCompositeKey(array $keys);

    public function getResultsOfStudent($id);
    public function getClassScheduleByStudentId($studentId, $startDate, $endDate);

    public function getClassScheduleByTeacherId($teacherId, $startDate, $endDate);

    public function getStudentCourseResultByCourseClassId($courseClassId);

    public function getCourseResultByStatus($studentId, $status);

    public function getAverageScoreByStudentId($studentId);

    public function totalCreditCompleted($studentId);

    public function totalCreditInProgress($studentId);
}
