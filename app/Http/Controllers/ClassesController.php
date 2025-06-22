<?php

namespace App\Http\Controllers;

use App\Exports\ClassesExport;
use App\Imports\ClassesImport;
use App\Http\Requests\ClassesRequest;
use App\Models\Classes;
use App\Models\Statistic;
use App\Repositories\Contracts\ClassesRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
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
        DB::beginTransaction();
        try {
            $data = $request->all();
            $response = $this->classesRepository->create($data);
            $response->{Classes::field('name')} = 'CLASS' . str_pad($response->{Classes::field('id')}, 4, '0', STR_PAD_LEFT);
            $response->save();
            Statistic::where('name', 'total_classes')->increment('value');
            DB::commit();
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
        DB::beginTransaction();
        try {
            $count = Classes::whereIn('class_id', $ids)->count();
            $response = $this->classesRepository->bulkDelete($ids, Classes::field('id'));
            Statistic::where('name', 'total_classes')->decrement('value', $count);
            DB::commit();
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa lớp học thành công !');
    }

    public function exportExcel()
    {
        $collection = $this->classesRepository->listWithFilter()->orderBy('class_id', 'desc')->get();
        try {
            return Excel::download(new ClassesExport($collection), 'classes.xlsx');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new ClassesImport($this->classesRepository), $file);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse('Success', 'Import lớp học thành công!');
    }
}
