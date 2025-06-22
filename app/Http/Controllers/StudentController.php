<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CoursePrerequisite;
use App\Models\RegistrationFeeDetail;
use App\Models\Student;
use App\Models\StudentCourseResult;
use App\Models\Payment;
use App\Repositories\Contracts\RegistrationFeeDetailRepositoryInterface;
use App\Repositories\Contracts\StudentCourseResultRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\StudentRepositoryImpl;
use App\Traits\ApiResponse;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\VNPayHelper;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use App\Exports\StudentExport;
use App\Imports\StudentImport;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    use ApiResponse;
    public function __construct(
        public StudentRepositoryInterface $studentRepository,
        public StudentCourseResultRepositoryInterface $studentCourseResultRepository,
        public RegistrationFeeDetailRepositoryInterface $registrationFeeDetailRepository,
        public UserRepositoryInterface $userRepository
    )
    {

    }

    public function getStudentWithUserInfo(Request $request)
    {
        try {
            $response = $this->studentRepository->getStudentWithUserInfo($request);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Thông tin sinh viên');
    }

    public function getStudentWithUserInfoById()
    {
        try {
            $userId = auth()->user()->id;
            $response = $this->studentRepository->getStudentWithUserInfoById($userId);
            $response->current_semester = $response->getCurrentSemester();
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
            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
            $studentId = $student->student_id;
            $courseClassId = $request->input('course_class_id');

            $courseClass = CourseClass::findOrFail($courseClassId);

            // Check if the student has already registered for this course class
            $existingRegistration = StudentCourseResult::where('student_id', $studentId)
                ->where('course_class_id', $courseClassId)
                ->first();

            if ($existingRegistration) {
                return $this->errorResponse('Bạn đã đăng ký học phần này', Response::HTTP_BAD_REQUEST);
            }

            //Check semester

            $currentSemester = $student->getCurrentSemester();

            if ($courseClass->semester > $currentSemester) {
                return $this->errorResponse('Chưa đến học kì đăng ký học phần này', Response::HTTP_BAD_REQUEST);
            }

            if($courseClass->current_student_count >= $courseClass->max_student_count) {
                return $this->errorResponse('Lớp học đã đủ số lượng sinh viên', Response::HTTP_BAD_REQUEST);
            }

            // Check prerequisites
            $prerequisites = CoursePrerequisite::where('course_id', $courseClass->course_id)->get();

            foreach ($prerequisites as $prerequisite) {
                if ($prerequisite->type == 1) { // Check prerequisite courses
                    $result = StudentCourseResult::where('student_id', $studentId)
                        ->where('student_id', $studentId)
                        ->where('course_class_id', $prerequisite->prerequisite_course_id)
                        ->where('status', 2)
                        ->first();
                    if (!$result) {
                        return $this->errorResponse('Không đủ điều kiện tiên quyết', Response::HTTP_BAD_REQUEST);
                    }
                } elseif ($prerequisite->type == 2) { // Check co-requisite courses
                    $registration = StudentCourseResult::where('student_id', $studentId)
                        ->where('student_id', $studentId)
                        ->where('course_class_id', $prerequisite->prerequisite_course_id)
                        ->whereIn('status', [1,2])
                        ->first();

                    if (!$registration) {
                        return $this->errorResponse('Không đủ điều kiện song hành', Response::HTTP_BAD_REQUEST);
                    }
                }
            }

            $courseClass->weekdays = json_decode($courseClass->weekdays, true);
            // Check schedule conflicts
            $registeredCourses = StudentCourseResult::where('student_id', $studentId)
                ->whereHas('courseClass', function ($query) use ($courseClass) {
                    $query->where('semester', $courseClass->semester);
                })
                ->get();
            foreach ($registeredCourses as $registeredCourse) {
                $registeredClass = $registeredCourse->courseClass;
                $registeredClass->weekdays = json_decode($registeredClass->weekdays, true); // chuyển thành mảng

                if (array_intersect($registeredClass->weekdays, $courseClass->weekdays)) {
                    if (
                        ($courseClass->lesson_start >= $registeredClass->lesson_start && $courseClass->lesson_start <= $registeredClass->lesson_end) ||
                        ($courseClass->lesson_end >= $registeredClass->lesson_start && $courseClass->lesson_end <= $registeredClass->lesson_end)
                    ) {
                        return $this->errorResponse('Trùng lịch học', Response::HTTP_BAD_REQUEST);
                    }
                }
            }
            // Register the course
            $data = $this->studentCourseResultRepository->create([
                'student_id' => $studentId,
                'course_class_id' => $courseClassId,
                'status' => 1, // Pending confirmation
            ]);

            // Create fee details
            $fee = [
                'student_id' => $studentId,
                'course_class_id' => $courseClassId,
                'fee_code' => 'FEE' . str_pad($courseClassId, 3, '0', STR_PAD_LEFT),
                'fee_name' => $courseClass->course->{Course::field('name')},
                'credit_count' => $courseClass->course->credit_hours,
                'unit_price' => 500000, // Example unit price
                'total_amount' => $courseClass->course->credit_hours * 500000,
                'status' => 0,
            ];

            $this->registrationFeeDetailRepository->create($fee);


            Student::where('student_id', $studentId)->increment('course_fee_debt', $fee['total_amount']);
            // Update current student count
            $courseClass->increment('current_student_count');

            return $this->successResponse(null, 'Đăng ký học phần thành công!');
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

    public function unregisterCourse($id)
    {

        $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
        $studentId = $student->student_id;
        $courseId = (int) $id;

        $registration = RegistrationFeeDetail::where('student_id', $studentId)
            ->where('course_class_id', $courseId)
            ->first();

        if ($registration) {
            $amount = (int) $registration->total_amount;
            if ($student->course_fee_debt - $amount < 0) {
                $remaining = $amount - $student->course_fee_debt;
                $student->update([
                    'course_fee_debt' => 0,
                    'wallet_balance' => $student->wallet_balance + $remaining,
                ]);
            } else {
                $student->decrement('course_fee_debt', $amount);
            }

            // Xóa chi tiết học phí
            RegistrationFeeDetail::where('student_id', $studentId)
                ->where('course_class_id', $courseId)
                ->delete();

            // Cập nhật số lượng sinh viên hiện tại của lớp học
            $courseClass = CourseClass::find($courseId);
            if ($courseClass) {
                $courseClass->decrement('current_student_count');
            }
        } else {
            return $this->errorResponse(null, 'Học phần không tồn tại hoặc đã bị hủy bỏ');
        }
        StudentCourseResult::where('student_id', $studentId)
            ->where('course_class_id', $courseId)
            ->delete();

        return $this->successResponse(null, 'Hủy học phần thành công!');
    }

    public function createMomoPayment(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'amount' => 'required|numeric|min:10000',
                'orderInfo' => 'required|string|max:255',
            ]);

            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
            $studentId = $student->student_id;

            // MoMo API credentials
            $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
            $partnerCode = 'MOMO';
            $accessKey = 'F8BBA842ECF85';
            $secretKey = 'K951B6PE1waDMi640xX08PD3vg6EkVlz';

            // Payment details
            $amount = $request->input('amount');
            $orderInfo = $request->input('orderInfo');
            $orderId = time() . ''; // Unique order ID
            $redirectUrl = "http://localhost:3000/student/payment";
            $ipnUrl = "https://ab3b-2401-d800-22-e128-3db9-74c8-711f-1668.ngrok-free.app/api/payment/momo/callback";
            $extraData = [
                'student_id' => $studentId,
                'amount' => $amount
            ];
            $extraDataEncrypted = base64_encode(json_encode($extraData));

            // Generate request ID
            $requestId = time() . '';

            // Generate signature
            $rawHash = "accessKey=" . $accessKey .
                "&amount=" . $amount .
                "&extraData=" . $extraDataEncrypted .
                "&ipnUrl=" . $ipnUrl .
                "&orderId=" . $orderId .
                "&orderInfo=" . $orderInfo .
                "&partnerCode=" . $partnerCode .
                "&redirectUrl=" . $redirectUrl .
                "&requestId=" . $requestId .
                "&requestType=captureWallet";

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            // Prepare request payload
            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "Test",
                'storeId' => "MomoTestStore",
                'requestType' => 'captureWallet',
                'requestId' => $requestId,
                'amount' => $amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraDataEncrypted,
                'signature' => $signature,
            ];

            // Execute POST request
            $result = $this->execPostRequest($endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);

            // Return the payment URL
            if (isset($jsonResult['payUrl'])) {
                return $this->successResponse(['payUrl' => $jsonResult['payUrl']], 'Payment URL generated successfully!');
            }

            return $this->errorResponse($jsonResult['message'] ?? 'Failed to generate payment URL', Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function handleMomoCallback(Request $request)
    {
        try {
            Log::info('MoMo Callback Data:', $request->all());
            $data = $request->all();

            // Kiểm tra chữ ký (nếu cần)
            // ...

            $resultCode = $data['resultCode'] ?? '';
            $extraData = json_decode(base64_decode($data['extraData'] ?? ''), true);
            $studentId = $extraData['student_id'] ?? null;
            $amount = $extraData['amount'] ?? 0;

            if ($resultCode == 0 && $studentId) { // 0 là thành công theo MoMo
                // Cộng tiền vào ví sinh viên
                $student = Student::find($studentId);
                if ($student) {
                    $student->wallet_balance += $amount;
                    $student->save();
                }
                Payment::create([
                    'student_id'    => $studentId,
                    'payment_date'  => now(),
                    'amount'        => $data['amount'],
                    'payment_method'=> 3, // 3: online payment
                    'status'        => 1, // 1: success
                    // Add more fields if your model/table supports them
                ]);
            }

            return response()->json(['success' => true]) ;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleMomoReturn(Request $request)
    {
        try {
            $studentId = $request->input('student_id');
            $resultCode = $request->input('resultCode');

            if ($resultCode == 0) { // Thanh toán thành công
                return redirect()->route('student.account')
                    ->with('success', 'Thanh toán thành công! 20,000 VND đã được cộng vào ví của bạn.');
            } else {
                return redirect()->route('student.account')
                    ->with('error', 'Thanh toán không thành công. Vui lòng thử lại.');
            }
        } catch (\Exception $e) {
            return redirect()->route('student.account')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    private function execPostRequest($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ]);

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

        // Execute POST
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function getAverageScore()
    {
        try {
            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
            $studentId = $student->student_id;

            $results = $this->studentCourseResultRepository->getAverageScoreByStudentId($studentId);

            return $this->successResponse($results, 'Điểm trung bình của sinh viên');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function exportExcel()
    {
        $response = $this->studentRepository->getStudentWithUserInfo();
        try {
            return Excel::download(new StudentExport($response), 'students.xlsx');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function importExcel(Request $request)
    {
        $file = $request->file('file');
        try {
            Excel::import(new StudentImport($this->studentRepository, $this->userRepository), $file);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse('Success', 'Import sinh viên thành công!');
    }

}
