<?php

namespace App\Http\Controllers;

use App\Exports\ClassroomExport;
use App\Http\Requests\ClassroomRequest;
use App\Imports\ClassroomImport;
use App\Models\Classes;
use App\Models\Classroom;
use App\Models\Statistic;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Maatwebsite\Excel\Facades\Excel;

class ClassroomController extends Controller
{
    use ApiResponse;

    public function __construct(
        public ClassroomRepositoryInterface $classroomRepository
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $classrooms = $this->classroomRepository->listWithFilter($request)->orderBy(Classroom::field('id'), 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($classrooms, 'Danh sách phòng học');
    }

    public function getClassroomActive(Request $request)
    {
        try {
            $classrooms = $this->classroomRepository->listWithFilter($request)
                ->where(Classroom::field('active'), CLASSROOM_STATUS_ACTIVE)
                ->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($classrooms, 'Danh sách phòng học đang hoạt động');
    }

    public function create(ClassroomRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $response = $this->classroomRepository->create($data);
            Statistic::where('name', 'total_classrooms')->increment('value');
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Thêm mới phòng học thành công!');
    }

    public function update(ClassroomRequest $request, $id)
    {
        try {
            $data = $request->all();
            $response = $this->classroomRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật phòng học thành công!');
    }

    public function updateActive(Request $request, $id)
    {
        $active = $request->get('active');
        try {
            $response = $this->classroomRepository->update($id, [Classroom::field('active') => $active]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái phòng học thành công!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        DB::beginTransaction();
        try {
            $count = Classroom::whereIn('classroom_id', $ids)->count();
            $response = $this->classroomRepository->bulkDelete($ids, Classroom::field('id'));
            Statistic::where('name', 'total_classrooms')->decrement('value', $count);
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa phòng học thành công!');
    }

    public function exportExcel()
    {
        $collection = $this->classroomRepository->listWithFilter()->get();
        try {
            return Excel::download(new ClassroomExport($collection), 'classrooms.xlsx');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', 500, $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new ClassroomImport($this->classroomRepository), $file);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', 422, $e->getMessage());
        }
        return $this->successResponse('Success', 'Import Classroom thành công !');
    }
}
