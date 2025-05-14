<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
                    $query->select('course_id', 'prerequisite_course_id', 'type');
                }])
                ->orderBy('created_at', 'desc')
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
        try {
            $data = $request->all();
            $prerequisites = $data['prerequisites'] ?? []; // Expecting an array of prerequisites
            $response = $this->courseRepository->create($data);
            $response->course_code = 'CO' . str_pad($response->course_id, 3, '0', STR_PAD_LEFT);
            $response->save();

            // Save prerequisites
            foreach ($prerequisites as $prerequisite) {
                if ($response->course_id == $prerequisite['values']['prerequisite_course_id']) {
                    return $this->errorResponse(
                        'Môn học và môn điều kiện không được trùng nhau',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $response->prerequisites()->create([
                    'prerequisite_course_id' => $prerequisite['values']['prerequisite_course_id'],
                    'type' => $prerequisite['values']['type']
                ]);
            }
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
                if ($response->course_id == $prerequisite['values']['prerequisite_course_id']) {
                    return $this->errorResponse(
                        'Môn học và môn điều kiện không được trùng nhau',
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $response->prerequisites()->create([
                    'prerequisite_course_id' => $prerequisite['values']['prerequisite_course_id'],
                    'type' => $prerequisite['values']['type']
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
        try {
            $response = $this->courseRepository->bulkDelete($ids, Course::id());
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Xóa môn học thành công!');
    }
}
