<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseClassRequest;
use App\Models\CourseClass;
use App\Repositories\Contracts\CourseClassRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseClassController extends Controller
{
    use ApiResponse;

    public function __construct(
        public CourseClassRepositoryInterface $courseClassRepository
    ) {}

    // ...existing code...

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $courseClasses = $this->courseClassRepository
                ->listWithFilter($request)
                ->orderBy(CourseClass::field('id'), 'desc')
                ->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($courseClasses, 'Danh sách lớp học phần');
    }

    public function create(CourseClassRequest $request)
    {
        try {
            $data = $request->all();

            // Convert weekdays to JSON string
            if (isset($data['weekdays']) && is_array($data['weekdays'])) {
                $data['weekdays'] = json_encode(array_map('intval', $data['weekdays']));
            }
            $response = $this->courseClassRepository->create($data);
            $response->{CourseClass::field('code')} = 'CC' . str_pad($response->{CourseClass::field('id')}, 3, '0', STR_PAD_LEFT);
            $response->save();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Lớp học phần được tạo thành công!');
    }

    public function update(CourseClassRequest $request, $id)
    {
        try {
            $data = $request->all();

            // Convert weekdays to JSON string
            if (isset($data['weekdays']) && is_array($data['weekdays'])) {
                $data['weekdays'] = json_encode(array_map('intval', $data['weekdays']));
            }
            $response = $this->courseClassRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Cập nhật lớp học phần thành công!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        try {
            $response = $this->courseClassRepository->bulkDelete($ids, CourseClass::field('id'));
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Xóa lớp học phần thành công!');
    }
}
