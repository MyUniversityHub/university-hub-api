<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassesRequest;
use App\Models\Classes;
use App\Repositories\Contracts\ClassesRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassesController extends Controller
{
    use ApiResponse;
    public function __construct(
        public ClassesRepositoryInterface $classesRepository
    )
    {

    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $redirects = $this->classesRepository->listWithFilter($request)->orderBy(Classes::field('id'), 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách lớp học');
    }

    public function getClassesActive()
    {
        try {
            $redirects = $this->classesRepository->listWithFilter()->where(Classes::field('active'), CLASS_STATUS_ACTIVE)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách lớp học');
    }

    public function getClassesByMajor($id)
    {
        try {
            $redirects = $this->classesRepository->listWithFilter()->where(Classes::field('majorId'), $id)->where(Classes::field('active'), CLASS_STATUS_ACTIVE)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách lớp học');
    }

    public function updateActive(Request $request, $id)
    {
        $active = $request->get('active');
        try {
            $response = $this->classesRepository->update($id, [Classes::field('active') => $active]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái lớp học thành công!');
    }

    public function create(ClassesRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->classesRepository->create($data);
            $response->{Classes::field('name')} = 'CLASS' . str_pad($response->{Classes::field('id')}, 4, '0', STR_PAD_LEFT);
            $response->save();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Thêm mới lớp học thành công !');
    }

    public function update(ClassesRequest $request, $id)
    {
        try {
            $data = $request->all();
            $response = $this->classesRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật lớp học thành công !');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        try {
            $response = $this->classesRepository->bulkDelete($ids, Classes::id());
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa lớp học thành công !');
    }
}
