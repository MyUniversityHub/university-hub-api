<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DepartmentController extends Controller
{
    use ApiResponse;
    public function __construct(
        public DepartmentRepositoryInterface $departmentRepository
    )
    {

    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $redirects = $this->departmentRepository->listWithFilter($request)->orderBy(Department::field('id'), 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách khoa');
    }

    public function getDepartmentsActive(Request $request)
    {
        try {
            $redirects = $this->departmentRepository->listWithFilter($request)->where('active', DEPARTMENT_STATUS_ACTIVE)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách khoa');
    }

    public function updateActive(Request $request, $id)
    {
        $active = $request->get('active');
        try {
            $response = $this->departmentRepository->update($id, [Department::field('active') => $active]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái khoa thành công!');
    }

    public function create(DepartmentRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->departmentRepository->create($data);
            $response->{Department::field('code')} = 'DE' . str_pad($response->{Department::field('id')}, 3, '0', STR_PAD_LEFT);
            $response->save();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Thêm mới khoa thành công !');
    }

    public function update(DepartmentRequest $request, $id)
    {
        try {
            $data = $request->all();
            $response = $this->departmentRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật khoa thành công !');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        try {
            $response = $this->departmentRepository->bulkDelete($ids, Department::id());
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa khoa thành công !');
    }
}
