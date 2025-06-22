<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface CourseRepositoryInterface extends BaseRepositoryInterface
{
    // Add any additional methods specific to the Course repository if needed
    public function getCoursesWithClassesByStudentMajor($request, $columns, $majorId);
}

