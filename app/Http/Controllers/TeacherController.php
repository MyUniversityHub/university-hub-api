<?php

namespace App\Http\Controllers;

use App\Exports\TeacherExport;
use App\Imports\TeacherImport;
use App\Models\Teacher;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    use ApiResponse;

    public function __construct(
        public TeacherRepositoryInterface $teacherRepository,
        public UserRepositoryInterface $userRepository
    ) {}

    public function getTeacherWithUserInfo(Request $request)
    {
        try {
            $response = $this->teacherRepository->getTeacherWithUserInfo($request);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Thông tin sinh viên');
    }

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

    public function getTeacherWithUserInfoById()
    {
        try {
            $userId = auth()->user()->id;
            $response = $this->teacherRepository->getTeacherWithUserInfoById($userId);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Thông tin giảng viên');
    }

    public function exportExcel()
    {
        $response = $this->userRepository->listWithFilter()->where('role_id', ROLE_TEACHER)->orderBy('updated_at', 'desc')->get();
        try {
            return Excel::download(new TeacherExport($response), 'students.xlsx');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new TeacherImport($this->teacherRepository, $this->userRepository), $file);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse('Success', 'Import giảng viên thành công!');
    }
}
