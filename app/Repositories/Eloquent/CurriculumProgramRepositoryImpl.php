<?php

namespace App\Repositories\Eloquent;

use App\Models\CurriculumProgram;
use App\Repositories\Contracts\CurriculumProgramRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CurriculumProgramRepositoryImpl extends BaseRepositoryImpl implements CurriculumProgramRepositoryInterface
{
    public function __construct(CurriculumProgram $model)
    {
        parent::__construct($model);
    }

    public function getAllCoursesInCurriculumProgram()
    {

    }

    public function getCoursesByMajorId($majorId)
    {
        return $this->model
            ->select(
                'curriculum_programs.major_id',
                'curriculum_programs.course_id',
                'curriculum_programs.semester',
                'courses.course_name',
                'courses.course_code',
                'courses.credit_hours',
            )
            ->join('courses', 'curriculum_programs.course_id', '=', 'courses.course_id')
            ->where('curriculum_programs.major_id', $majorId)
            ->orderBy('curriculum_programs.semester', 'asc')
            ->orderBy('courses.course_name', 'asc')
            ->get()
            ->map(function ($item) {
                // Get all prerequisites for each course
                $prerequisites = \DB::table('course_prerequisites as pre')
                    ->join('courses as pc', 'pre.prerequisite_course_id', '=', 'pc.course_id')
                    ->where('pre.course_id', $item->course_id)
                    ->select(
                        'pre.prerequisite_course_id as prerequisite_id',
                        'pc.course_code as prerequisite_code',
                        'pc.course_name as prerequisite_name'
                    )
                    ->get();

                if ($prerequisites->isNotEmpty()) {
                    $item->prerequisite_id = $prerequisites->pluck('prerequisite_id')->toArray();
                    $item->prerequisite_code = $prerequisites->pluck('prerequisite_code')->implode(', ');
                    $item->prerequisite_name = $prerequisites->pluck('prerequisite_name')->implode(', ');
                } else {
                    $item->prerequisite_id = null;
                    $item->prerequisite_code = null;
                    $item->prerequisite_name = null;
                }

                // Check if course has any active classes (status = 2)
                $hasActiveClasses = \DB::table('course_classes')
                    ->where('course_id', $item->course_id)
                    ->where('status', 2)
                    ->exists();

                $item->has_active_classes = $hasActiveClasses;

                return $item;
            });
    }

    public function deleteByCompositeKey($majorId, $courseId)
    {
        return $this->model
            ->where('major_id', $majorId)
            ->where('course_id', $courseId)
            ->delete();
    }


}
