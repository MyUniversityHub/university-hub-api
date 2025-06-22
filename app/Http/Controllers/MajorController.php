<?php

namespace App\Http\Controllers;

use App\Exports\DepartmentExport;
use App\Exports\MajorExport;
use App\Http\Requests\MajorRequest;
use App\Imports\MajorImport;
use App\Models\Major;
use App\Models\Statistic;
use App\Repositories\Contracts\MajorRepositoryInterface;
use App\Repositories\Eloquent\MajorRepositoryImpl;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;

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
        DB::beginTransaction();
        $active = $request->get('active');
        try {
            $response = $this->majorRepository->update($id, [Major::field('active') => $active]);
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái chuyên ngành thành công!');
    }

    public function create(MajorRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $response = $this->majorRepository->create($data);
            $response->{Major::field('code')} = 'MA' . str_pad($response->{Major::field('id')}, 4, '0', STR_PAD_LEFT);
            $response->save();
            Statistic::where('name', 'total_majors')->increment('value');
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Thêm mới chuyên ngành thành công !');
    }

    public function update(MajorRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $response = $this->majorRepository->update($id, $data);
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật chuyên ngành thành công !');
    }

    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        $ids = $request->input('ids');
        try {
            $count = Major::whereIn('major_id', $ids)->count();
            $response = $this->majorRepository->bulkDelete($ids, Major::field('id'));
            Statistic::where('name', 'total_majors')->decrement('value', $count);
            DB::commit();
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa chuyên ngành thành công !');
    }

    public function exportExcel()
    {
        $collection = $this->majorRepository->listWithFilter()->orderBy('major_id', 'desc')->get();
        try {
            return Excel::download(new MajorExport($collection), 'majors.xlsx');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new MajorImport($this->majorRepository), $file);
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse('Success', 'Import Redirect thành công !');
    }


}
