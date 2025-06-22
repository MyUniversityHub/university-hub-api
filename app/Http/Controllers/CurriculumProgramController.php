<?php

namespace App\Http\Controllers;

use App\Models\CurriculumProgram;
use App\Repositories\Contracts\CurriculumProgramRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CurriculumProgramController extends Controller
{
    use ApiResponse;

    public function __construct(
        public CurriculumProgramRepositoryInterface $curriculumProgramRepository
    ) {}

    public function index(Request $request)
    {
        try {
            $items = $this->curriculumProgramRepository->listWithFilter($request)
                ->orderBy(CurriculumProgram::field('semester'), 'asc')
                ->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($items, 'Danh sách chương trình đào tạo');
    }

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $exists = DB::table('curriculum_programs')
                ->where('major_id', $data['major_id'])
                ->where('course_id', $data['course_id'])
                ->exists();

            if ($exists) {
                return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY,'Môn học đã được thêm vào chương trình đào tạo này.');
            }
            $item = $this->curriculumProgramRepository->create($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($item, 'Thêm mới chương trình đào tạo thành công!');
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $item = $this->curriculumProgramRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($item, 'Cập nhật chương trình đào tạo thành công!');
    }

    public function delete(Request $request)
    {
        $courseId = $request->input('course_id');
        $majorId = $request->input('major_id');
        try {
            $response = $this->curriculumProgramRepository->deleteByCompositeKey(
                $majorId,
                $courseId,
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa chương trình đào tạo thành công!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        try {
            $response = $this->curriculumProgramRepository->bulkDelete($ids, [CurriculumProgram::field('majorId'), CurriculumProgram::field('courseId'), CurriculumProgram::field('semester')]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa chương trình đào tạo thành công!');
    }

    public function getCurriculumProgramByMajor($id)
    {
        try {
            $items = $this->curriculumProgramRepository->getCoursesByMajorId($id);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($items, 'Danh sách chương trình đào tạo theo chuyên ngành');
    }
}
