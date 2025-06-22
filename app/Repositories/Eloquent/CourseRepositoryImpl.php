<?php

namespace App\Repositories\Eloquent;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Eloquent\Filters\CourseFilter;
use Illuminate\Http\Request;

class CourseRepositoryImpl extends BaseRepositoryImpl implements CourseRepositoryInterface
{
    public function __construct(Course $model, CourseFilter $filter)
    {
        parent::__construct($model, $filter);
    }

    public function getCoursesWithClassesByStudentMajor($request, $columns, $majorId) {
        $query = $this->model->newQuery()
            ->select($columns)
        ->whereHas('courseClasses', function ($query) use ($majorId) {
            $query->where('major_id', $majorId);
        })->with(['courseClasses' => function ($query) use ($majorId) {
            $query->where('major_id', $majorId);
        }]);

        if (!$this->filter) {
            return $query;
        }

        return $this->filter->apply($query, $request);
    }
}
