<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Eloquent\StudentRepositoryImpl;
use App\Traits\ApiResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentController extends Controller
{
    use ApiResponse;
    public function __construct(
        public StudentRepositoryInterface $studentRepository
    )
    {

    }

    public function getStudentWithUserInfo()
    {
        try {
            $response = $this->studentRepository->getStudentWithUserInfo();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Thông tin sinh viên');
    }

    public function getStudentWithUserInfoById($id)
    {
        try {
            $response = $this->studentRepository->getStudentWithUserInfoById($id);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Thông tin sinh viên');
    }

    public function update($id, Request $request)
    {
        try {
            $data = $request->all();
            $response = $this->studentRepository->update($id, $data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Cập nhật thông tin sinh viên thành công!');
    }

    public function registerCourse(Request $request)
    {
        try {
            // Get the data from the request
            $studentId = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id)->{Student::field('id')};

            $courseClassId = $request->input('course_class_id');
            // Call the repository to register the course
            $response = $this->studentRepository->registerCourse(['student_id' => $studentId, 'course_class_id' => $courseClassId]);

            return $this->successResponse($response, 'Đăng ký học phần thành công!');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function uploadImage(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'image' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Get the file
            $file = $request->file('image');

            // Check if the file is valid
            if (!$file->isValid()) {
                return $this->errorResponse('Invalid file upload', Response::HTTP_BAD_REQUEST);
            }

            // Move the file to a permanent location
            $filePath = $file->store('uploads', 'local');

            // Upload to Cloudinary
            $uploadedFile = Cloudinary::upload(storage_path('app/' . $filePath));

            // Check if the response is valid
            if (!$uploadedFile) {
                return $this->errorResponse('Failed to upload image to Cloudinary', Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $uploadedFileUrl = $uploadedFile->getSecurePath();

            return $this->successResponse(['url' => $uploadedFileUrl], 'Image uploaded successfully!');
        } catch (\Exception $e) {
            return $this->errorResponse('Error uploading image', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }
}
