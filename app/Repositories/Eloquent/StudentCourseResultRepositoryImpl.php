<?php

namespace App\Repositories\Eloquent;

use App\Models\StudentCourseResult;
use App\Repositories\Contracts\StudentCourseResultRepositoryInterface;
use App\Repositories\Eloquent\Filters\StudentCourseResultFilter;

class StudentCourseResultRepositoryImpl extends BaseRepositoryImpl implements StudentCourseResultRepositoryInterface
{
    public function __construct(StudentCourseResult $model)
    {
        parent::__construct($model);
    }

    public function updateCompositeKey(array $keys, array $data)
    {
        return $this->model->where($keys)->update($data);
    }

    public function deleteCompositeKey(array $keys)
    {
        return $this->model->where($keys)->delete();
    }

    public function getResultsOfStudent($id)
    {
        return $this->model->select('student_course_results.*', 'courses.course_name', 'course_classes.course_class_code')
            ->join('course_classes', 'course_classes.course_class_id', '=', 'student_course_results.course_class_id')
            ->join('courses', 'courses.course_id', '=', 'course_classes.course_id')
            ->where('student_course_results.student_id', $id)
            ->get();
    }

    public function getClassScheduleByStudentId($studentId, $startDate, $endDate)
    {
        return $this->model->select('courses.course_name', 'course_classes.course_class_code', 'course_classes.course_class_id', 'course_classes.weekdays', 'course_classes.lesson_start', 'course_classes.lesson_end', 'users.name as teacher_name', 'classrooms.room_name')
            ->join('course_classes', 'course_classes.course_class_id', '=', 'student_course_results.course_class_id')
            ->join('courses', 'courses.course_id', '=', 'course_classes.course_id')
            ->join('teachers', 'teachers.teacher_id', '=', 'course_classes.teacher_id')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->join('classrooms', 'classrooms.classroom_id', '=', 'course_classes.classroom_id')
            ->where('student_course_results.student_id', $studentId)
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

    public function getStudentCourseResultByCourseClassId($courseClassId)
    {
        return $this->model->where('course_class_id', $courseClassId)
            ->join('students', 'students.student_id', '=', 'student_course_results.student_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->select('student_course_results.*', 'students.student_code', 'users.name as student_name')
            ->get();
    }

    public function getClassScheduleByTeacherId($teacherId, $startDate, $endDate)
    {
        return $this->model->select('courses.course_name', 'course_classes.course_class_code', 'course_classes.weekdays', 'course_classes.lesson_start', 'course_classes.lesson_end', 'users.name as teacher_name', 'classrooms.room_name')
            ->join('course_classes', 'course_classes.course_class_id', '=', 'student_course_results.course_class_id')
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

    public function getCourseResultByStatus($studentId, $status)
    {
        return $this->model
            ->select('student_course_results.*', 'courses.course_name', 'courses.credit_hours')
            ->join('course_classes', 'course_classes.course_class_id', '=', 'student_course_results.course_class_id')
            ->join('courses', 'courses.course_id', '=', 'course_classes.course_id')
            ->where('student_course_results.status', $status)
            ->where('student_course_results.student_id', $studentId)
            ->get();
    }

    public function getAverageScoreByStudentId($studentId)
    {
        try {
            // Get completed courses with valid scores and their credit hours
            $results = $this->model
                ->select('student_course_results.average_score', 'courses.credit_hours')
                ->join('course_classes', 'course_classes.course_class_id', '=', 'student_course_results.course_class_id')
                ->join('courses', 'courses.course_id', '=', 'course_classes.course_id')
                ->where('student_course_results.student_id', $studentId)
                ->where('student_course_results.status', 2) // Only completed courses
                ->whereNotNull('student_course_results.average_score')
                ->get();

            // If no results, return 0
            if (!is_object($results) || count($results) === 0) {
                return 0;
            }

            $totalWeightedScore = 0;
            $totalCredits = 0;
            foreach ($results as $result) {
                // Convert to 4.0 scale before multiplying by credit hours
                $score4Scale = $this->convertTo4Scale($result->average_score);
                $totalWeightedScore += $score4Scale * $result->credit_hours;
                $totalCredits += $result->credit_hours;
            }

            return $totalCredits > 0 ? round($totalWeightedScore / $totalCredits, 2) : 0;
        } catch (\Exception $e) {
            \Log::error("Error calculating average score: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Converts a score from 10-point scale to 4.0 scale
     */
    private function convertTo4Scale($averageScore)
    {
        if ($averageScore >= 8.5) return 4.0;
        if ($averageScore >= 8.0) return 3.5;
        if ($averageScore >= 7.0) return 3.0;
        if ($averageScore >= 6.5) return 2.5;
        if ($averageScore >= 5.5) return 2.0;
        if ($averageScore >= 5.0) return 1.5;
        if ($averageScore >= 4.0) return 1.0;
        return 0.0;
    }


}
