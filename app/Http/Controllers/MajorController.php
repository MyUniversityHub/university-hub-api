<?php

namespace App\Http\Controllers;

use App\Http\Requests\MajorRequest;
use App\Models\Major;
use App\Repositories\Contracts\MajorRepositoryInterface;
use App\Repositories\Eloquent\MajorRepositoryImpl;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MajorController extends Controller
{
    use ApiResponse;
    public function __construct(
        public MajorRepositoryInterface $majorRepository
    )
    {

    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $redirects = $this->majorRepository->listWithFilter($request)->orderBy(Major::field('id'), 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách chuyên ngành');
    }

    public function getMajorsActive(Request $request)
    {
        try {
            $redirects = $this->majorRepository->listWithFilter($request)->where(Major::field('active'), MAJOR_STATUS_ACTIVE)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách khoa');
    }

    public function getMajorsByDepartment($id)
    {
        try {
            $redirects = $this->majorRepository->listWithFilter()->where(Major::field('departmentId'), $id)->where(Major::field('active'), MAJOR_STATUS_ACTIVE)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách khoa');
    }

    public function updateActive(Request $request, $id)
    {
        $active = $request->get('active');
        try {
            $response = $this->majorRepository->update($id, [Major::field('active') => $active]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái chuyên ngành thành công!');
    }

    public function create(MajorRequest $request)
    {
        try {
            $data = $request->all();
            $response = $this->majorRepository->create($data);
            $response->{Major::field('code')} = 'MA' . str_pad($response->{Major::field('id')}, 4, '0', STR_PAD_LEFT);
            $response->save();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Thêm mới chuyên ngành thành công !');
    }

    public function update(MajorRequest $request, $id)
    {
        try {
            $data = $request->all();
            $response = $this->majorRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật chuyên ngành thành công !');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        try {
            $response = $this->majorRepository->bulkDelete($ids, Major::field('id'));
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa chuyên ngành thành công !');
    }
}
