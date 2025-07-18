<?php

namespace App\Http\Controllers;

use App\Exports\CourseExport;
use App\Http\Requests\CourseRequest;
use App\Imports\CourseImport;
use App\Models\Course;
use App\Models\Major;
use App\Models\Statistic;
use App\Models\Student;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class CourseController extends Controller
{
    use ApiResponse;

    public function __construct(
        public CourseRepositoryInterface $courseRepository
    ) {}

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $courses = $this->courseRepository->listWithFilter($request)
                ->with(['prerequisites' => function ($query) {
                    $query->select('course_id', 'prerequisite_course_id');
                }])
                ->orderBy('course_id', 'desc')
                ->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($courses, 'Danh sách môn học');
    }


    public function getCourseActive(Request $request)
    {
        try {
            $courses = $this->courseRepository->listWithFilter($request)
                ->where(Course::field('active'), COURSE_STATUS_ACTIVE)
                ->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($courses, 'Danh sách môn học đang hoạt động');
    }

    public function create(CourseRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $prerequisites = $data['prerequisites'] ?? []; // Expecting an array of prerequisites
            $response = $this->courseRepository->create($data);
            $response->course_code = 'CO' . str_pad($response->course_id, 3, '0', STR_PAD_LEFT);
            $response->save();

            // Save prerequisites
            foreach ($prerequisites as $prerequisite) {
                if ($response->course_id == $prerequisite) {
                    return $this->errorResponse(
                        'Môn học và môn điều kiện không được trùng nhau',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $response->prerequisites()->create([
                    'prerequisite_course_id' => $prerequisite,
                ]);
            }
            Statistic::where('name', 'total_courses')->increment('value');
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Thêm mới môn học thành công!');
    }

    public function update(CourseRequest $request, $id)
    {
        try {
            $data = $request->all();
            $prerequisites = $data['prerequisites'] ?? []; // Expecting an array of prerequisites

            // Update course details
            $response = $this->courseRepository->update($id, $data);

            // Update prerequisites
            $course = Course::findOrFail($id);
            $course->prerequisites()->delete(); // Remove existing prerequisites

            // Save prerequisites
            foreach ($prerequisites as $prerequisite) {
                if ($response->course_id == $prerequisite) {
                    return $this->errorResponse(
                        'Môn học và môn điều kiện không được trùng nhau',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $response->prerequisites()->create([
                    'prerequisite_course_id' => $prerequisite,
                ]);
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Cập nhật môn học thành công!');
    }


    public function updateActive(Request $request, $id)
    {
        $active = $request->get('active');
        try {
            $response = $this->courseRepository->update($id, [Course::field('active') => $active]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái chuyên ngành thành công!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        DB::beginTransaction();
        try {
            $count = Course::whereIn('course_id', $ids)->count();
            $response = $this->courseRepository->bulkDelete($ids, Course::field('id'));
            Statistic::where('name', 'total_courses')->decrement('value', $count);
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Xóa môn học thành công!');
    }

    public function getCoursesWithClassesByStudentMajor(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            // Get the authenticated student's information
            $student = auth()->user()->student;
            if (!$student) {
                return $this->errorResponse('Student not found', Response::HTTP_NOT_FOUND);
            }

            // Get the major ID of the student
            $majorId = $student->class->major->{Major::field('id')};
            // Query courses that have course classes belonging to the student's major
            $courses = $this->courseRepository->getCoursesWithClassesByStudentMajor($request, ['*'], $majorId)->paginate($perPage);

            return $this->successResponse($courses, 'Danh sách môn học có lớp học phần thuộc chuyên ngành của sinh viên');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $collection = $this->courseRepository->listWithFilter()->get();
        try {
            return Excel::download(new CourseExport($collection), 'courses.xlsx');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', 500, $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new CourseImport($this->courseRepository), $file);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', 422, $e->getMessage());
        }
        return $this->successResponse('Success', 'Import Course thành công !');
    }

}
