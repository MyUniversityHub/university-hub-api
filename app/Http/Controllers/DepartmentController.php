<?php

namespace App\Http\Controllers;

use App\Exports\DepartmentExport;
use App\Http\Requests\DepartmentRequest;
use App\Imports\DepartmentImport;
use App\Models\Department;
use App\Models\Statistic;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
//use Vtiful\Kernel\Excel;

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
        DB::beginTransaction();
        $active = $request->get('active');
        try {
            $response = $this->departmentRepository->update($id, [Department::field('active') => $active]);
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái khoa thành công!');
    }

    public function create(DepartmentRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $response = $this->departmentRepository->create($data);
            $response->{Department::field('code')} = 'DE' . str_pad($response->{Department::field('id')}, 3, '0', STR_PAD_LEFT);
            $response->save();
            Statistic::where('name', 'total_departments')->increment('value');
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Thêm mới khoa thành công !');
    }

    public function update(DepartmentRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $response = $this->departmentRepository->update($id, $data);
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật khoa thành công !');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        DB::beginTransaction();
        try {
            $count = Department::whereIn('department_id', $ids)->count();
            $response = $this->departmentRepository->bulkDelete($ids, Department::field('id'));
            Statistic::where('name', 'total_departments')->decrement('value', $count);
            DB::commit();
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa khoa thành công !');
    }

    public function exportExcel()
    {
        $collection = $this->departmentRepository->listWithFilter()->orderBy('department_id', 'desc')->get();
        try {
            return Excel::download(new DepartmentExport($collection), 'departments.xlsx');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new DepartmentImport($this->departmentRepository), $file);
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse('Success', 'Import Redirect thành công !');
    }
}
