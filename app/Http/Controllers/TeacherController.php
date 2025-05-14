<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TeacherController extends Controller
{
    use ApiResponse;

    public function __construct(
        public TeacherRepositoryInterface $teacherRepository
    ) {}

    public function getTeacherActive(Request $request)
    {
        try {
            $classrooms = $this->teacherRepository->listWithFilter($request)
                ->where(Teacher::field('active'), CLASSROOM_STATUS_ACTIVE)
                ->with('user:id,name')
                ->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($classrooms, 'Danh sách phòng học đang hoạt động');
    }
}
