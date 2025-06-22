<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentRequest;
use App\Http\Requests\UserRequest;
use App\Mail\UserRegisteredMail;
use App\Mail\UserResetPasswordMail;
use App\Models\Statistic;
use App\Models\Student;
use App\Models\Teacher;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\TeacherRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use ApiResponse;
    public function __construct(
        public UserRepositoryInterface $userRepository,
        public StudentRepositoryInterface $studentRepository,
        public TeacherRepositoryInterface $teacherRepository
    )
    {

    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $response = $this->userRepository->listWithFilter($request)->orderBy('updated_at', 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Danh sách người dùng');
    }

    public function getStudents(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $response = $this->userRepository->listWithFilter($request)->where('role_id', ROLE_STUDENT)->orderBy('updated_at', 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Danh sách người tài khoản của sinh viên');
    }

    public function getTeachers(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $response = $this->userRepository->listWithFilter($request)->where('role_id', ROLE_TEACHER)->orderBy('updated_at', 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Danh sách người tài khoản của giảng viên');
    }

    public function getAdmins(Request $request)
    {
        $perPage = $request->get('per_page', LIST_LIMIT_PAGINATION);
        try {
            $response = $this->userRepository->listWithFilter($request)->where('role_id', ROLE_ADMIN)->orderBy('updated_at', 'desc')->paginate($perPage);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Danh sách người tài khoản của quản trị viên');
    }

    public function create(CreateStudentRequest $request)
    {
        DB::beginTransaction();
        try {
            $userData = $request->all();
            // Tạo mật khẩu ngẫu nhiên nếu không có
            $userData['user_name'] = 'student';
            if (empty($userData['password'])) {
                $password = $this->generateRandomPassword();
                $userData['password'] = $password;
            } else {
                $password = $userData['password']; // Lưu lại mật khẩu gốc để gửi email
            }

            // Mã hóa mật khẩu
            $userData['password'] = Hash::make($userData['password']);
            $userData['active'] = 1;

            $user = $this->userRepository->create($userData);
            if (!$user) {
                return $this->errorResponse('Đăng ký tài khoản thất bại!', Response::HTTP_UNAUTHORIZED, 'Error register');
            }
            if ($user->role_id === ROLE_STUDENT) {
                $student = $this->studentRepository->create([
                    'user_id' => $user->id,
                    'class_id' => $userData['class_id'],
                    'admission_year' => $userData['admission_year'],
                ]);
                Statistic::where('name', 'total_students')->increment('value');
                $student->student_code = 'STUDENT' . str_pad($student->student_id, 3, '0', STR_PAD_LEFT);
                $student->save();
                $user->user_name = $student->student_code;
                $user->save();
            }

            if ($user->role_id === ROLE_TEACHER) {
                $teacher = $this->teacherRepository->create([
                    'user_id' => $user->id,
                    'department_id' => $userData['department_id']
                ]);
                Statistic::where('name', 'total_teachers')->increment('value');
                $teacher->teacher_code = 'TEACHER' . str_pad($teacher->teacher_id, 3, '0', STR_PAD_LEFT);
                $teacher->save();
                $user->user_name = $teacher->teacher_code;
                $user->save();
            }
            // Gửi email cho người dùng
            Mail::to($user->email)->send(new UserRegisteredMail($user, $password));

            DB::commit();
            return $this->successResponse($user, 'Tài khoản đã được đăng ký thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Đăng ký tài khoản thất bại!', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }


    public function update(UserRequest $request, $id)
    {
        DB::beginTransaction();
        try {

            $userData = $request->all();
            // Cập nhật User
            $user = $this->userRepository->update($id, $userData);
            if (!$user) {
                return $this->errorResponse('Cập nhật tài khoản thất bại!', Response::HTTP_UNAUTHORIZED, 'Error update');
            }

            // Xử lý Student nếu role là ROLE_STUDENT
            if ($user->role_id === ROLE_STUDENT) {
                $studentData = [
                    'class_id' => $userData['class_id'],
                ];
                $user['student'] =  $this->studentRepository->update($user->student->{Student::field('id')}, $studentData);
                $user->load('student');
            }

            // Xử lý Teacher nếu role là ROLE_TEACHER
            if ($user->role_id === ROLE_TEACHER) {
                $teacherData = [
                    'department_id' => $userData['department_id'],
                ];

                $user['teacher'] = $this->teacherRepository->update($user->teacher->{Teacher::field('id')}, $teacherData);
                $user->load('teacher');
            }

            DB::commit();
            return $this->successResponse($user, 'Tài khoản đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Cập nhật tài khoản thất bại!', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function resetPassword($id)
    {
        DB::beginTransaction();
        $password = $this->generateRandomPassword();
        $hashPass = Hash::make($password);
        $user = $this->userRepository->find($id);
        try {
            $response = $this->userRepository->update($id, ['password' => $hashPass]);
            Mail::to($user->email)->send(new UserResetPasswordMail($user, $password));

            DB::commit();
            return $this->successResponse($response, 'Đặt lại mật khẩu cho người dùng thành công !');
        }catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function updateActive(Request $request, $id)
    {
        $active = $request->get('active');
        try {
            $response = $this->userRepository->update($id, ['active' => $active]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Cập nhật trạng thái người dùng thành công!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        $role = $request->input('role', null);
        DB::beginTransaction();
        try {
            $count = Student::whereIn('user_id', $ids)->count();
            $response = $this->userRepository->bulkDelete($ids, 'id');
            if($role == 'STUDENT') {
                $this->studentRepository->bulkSoftDeleteByUserIds($ids);
                Statistic::where('name', 'total_students')->decrement('value', $count);
            }

            if($role == 'TEACHER') {
                $this->teacherRepository->bulkSoftDeleteByUserIds($ids);
                Statistic::where('name', 'total_teachers')->decrement('value', $count);
            }
            DB::commit();
        }catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($response, 'Xóa người dùng thành công !');
    }

    private function generateRandomPassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        $maxIndex = strlen($chars) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)];
        }

        return $password;
    }

    public function getNotifications()
    {
        $userId = auth()->user()->id;
        try {
            $notifications = DB::table('notification')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->successResponse($notifications, 'Danh sách thông báo của người dùng');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function updateStatusNotification()
    {
        $userId = auth()->user()->id;
        try {
            $notification = DB::table('notification')->where('user_id', $userId)->get();
            if (!$notification) {
                return $this->errorResponse('Thông báo không tồn tại', Response::HTTP_NOT_FOUND);
            }

            DB::table('notification')->where('user_id', $userId)->update(['status' => 1]); // 1: Đã đọc

            return $this->successResponse(null, 'Cập nhật trạng thái thông báo thành công');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }
}
