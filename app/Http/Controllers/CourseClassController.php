<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseClassRequest;
use App\Models\Classes;
use App\Models\CourseClass;
use App\Models\Statistic;
use App\Models\Student;
use App\Models\StudentCourseResult;
use App\Models\Teacher;
use App\Repositories\Contracts\ClassesRepositoryInterface;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use App\Repositories\Contracts\CourseClassRepositoryInterface;
use App\Repositories\Contracts\MajorRepositoryInterface;
use App\Repositories\Contracts\StudentCourseResultRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CourseClassController extends Controller
{
    use ApiResponse;

    public function __construct(
        public CourseClassRepositoryInterface $courseClassRepository,
        public StudentRepositoryInterface $studentRepository,
        public UserRepositoryInterface $userRepository,
        public TeacherRepositoryInterface $teacherRepository,
        public ClassesRepositoryInterface $classesRepository,
        public MajorRepositoryInterface $majorRepository,
        public StudentCourseResultRepositoryInterface $studentCourseResultRepository
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

    public function bulkCreate(Request $request)
    {
        try {
            $inputData = $request->all();
            $createdClasses = [];

            // Kiểm tra nếu đầu vào là một item đơn lẻ thì chuyển thành mảng
            $classesData = isset($inputData['id']) ? [$inputData] : $inputData;

            foreach ($classesData as $classData) {
                $data = $classData['values'];

                // Convert weekdays to JSON string nếu có
                if (isset($data['weekdays']) && is_array($data['weekdays'])) {
                    $data['weekdays'] = json_encode(array_map('intval', $data['weekdays']));
                }

                // Tạo course class
                $response = $this->courseClassRepository->create($data);

                // Generate code
                $response->{CourseClass::field('code')} = 'CC' . str_pad($response->{CourseClass::field('id')}, 3, '0', STR_PAD_LEFT);
                $response->save();

                $createdClasses[] = $response;
            }

            $message = count($createdClasses) > 1
                ? 'Các lớp học phần đã được tạo thành công!'
                : 'Lớp học phần đã được tạo thành công!';

            return $this->successResponse($createdClasses, $message);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
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

    public function openCourseClass($id)
    {
        DB::beginTransaction();
        try {
            $response = $this->courseClassRepository->update($id, [CourseClass::field('status') => COURSE_CLASS_STATUS_OPEN]);
            Statistic::where('name', 'total_course_classes')->increment('value');
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Mở lớp thành công!');
    }

    public function closeCourseClass($id)
    {
        DB::beginTransaction();
        try {
            $response = $this->courseClassRepository->update($id, [CourseClass::field('status') => COURSE_CLASS_STATUS_CLOSE]);
            Statistic::where('name', 'total_course_classes')->decrement('value');
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Đóng lớp thành công!');
    }

    public function assignTeacher(CourseClassRequest $request, $id)
    {
        try {
            $data = $request->all();
            $data['status'] = COURSE_CLASS_STATUS_READY_TO_OPEN;
            // Convert weekdays to JSON string
            if (isset($data['weekdays']) && is_array($data['weekdays'])) {
                $data['weekdays'] = json_encode(array_map('intval', $data['weekdays']));
            }
            $response = $this->courseClassRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Phân công giảng viên thành công!');
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

    public function getCourseClassesForStudent()
    {
        try {
            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);

            // Kiểm tra nếu không có admission_year thì báo lỗi
            if (empty($student->admission_year)) {
                throw new \Exception('Sinh viên chưa có thông tin năm nhập học');
            }

            $classId = $student->{Student::field('classId')} ?? null;
            $class = $this->classesRepository->find($classId);
            $majorId = $class->{Classes::field('majorId')} ?? null;

            if (!$classId) {
                throw new \Exception('Sinh viên chưa được phân vào lớp nào');
            }

            $courses = CourseClass::where(CourseClass::field('majorId'), $majorId)
                ->get();

            return $this->successResponse($courses, 'Danh sách môn học phù hợp');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function getCourseClassesForTeacher(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $teacher = $this->teacherRepository->findBy(Teacher::field('userId'), auth()->user()->id);
            $teacherId = $teacher->{Teacher::field('id')};
            $courseClasses = $this->courseClassRepository->listWithFilter()->where(CourseClass::field('teacherId'), $teacherId)
                ->orderBy(CourseClass::field('id'), 'desc')
                ->paginate($perPage);

            return $this->successResponse($courseClasses, 'Danh sách lớp học phần của giảng viên');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function getStudentCourseResultByCourseClassId($id)
    {
        try {
            $students = $this->studentCourseResultRepository->getStudentCourseResultByCourseClassId($id);

            return $this->successResponse($students, 'Danh sách sinh viên trong lớp học phần');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function getClassesByCourseAndMajor($id)
    {
        try {
            $id = (int) $id;
            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);

            // Kiểm tra nếu không có admission_year thì báo lỗi
            if (empty($student->admission_year)) {
                throw new \Exception('Sinh viên chưa có thông tin năm nhập học');
            }

            $classId = $student->{Student::field('classId')} ?? null;
            $class = $this->classesRepository->find($classId);
            $majorId = $class->{Classes::field('majorId')} ?? null;

            $classes = $this->courseClassRepository->getClassesByCourseAndMajor($id, $majorId);

            return $this->successResponse($classes, 'Danh sách lớp học phần theo môn học và chuyên ngành');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function getTeachingSchedule(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $teacherId = $this->userRepository->find(auth()->user()->id)->teacher->{Teacher::field('id')};
        try {
            $schedules = $this->courseClassRepository->getClassScheduleByTeacherId($teacherId, $startDate, $endDate);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($schedules, 'Lịch dạy của giảng viên');
    }
}
