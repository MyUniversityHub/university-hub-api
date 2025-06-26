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
use Illuminate\Support\Facades\DB;
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
        public UserRepositoryInterface $userRepository,

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
        DB::beginTransaction();
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
                if($existingRegistration->status != 0) {
                    return $this->errorResponse('Bạn đã đăng ký học phần này', Response::HTTP_BAD_REQUEST);
                }
//                else {
//                    // If the registration is pending, allow re-registration
//                    $existingRegistration->status = 1; // Set status to pending confirmation
//                    $existingRegistration->frequent_score_1 = null;
//                    $existingRegistration->frequent_score_2 = null;
//                    $existingRegistration->frequent_score_3 = null;
//                    $existingRegistration->final_score = null;
//                    $existingRegistration->absent_sessions = 0;
//                    $existingRegistration->save();
//                    return $this->successResponse(null, 'Đăng ký học phần thành công!');
//                }
            }

            if($courseClass->status != COURSE_CLASS_STATUS_OPEN) {
                return $this->errorResponse('Lớp học phần không mở đăng ký', Response::HTTP_BAD_REQUEST);
            }

            //Check semester

            $currentSemester = $student->getCurrentSemester();

            $totalCreditsProgressed = $this->studentCourseResultRepository->totalCreditInProgress($studentId);
            // Check if the total credits exceeded the limit

            if ($totalCreditsProgressed + $courseClass->course->credit_hours > env('CREDIT_HOURSE_MAX')) {
                return $this->errorResponse('Tổng số tín chỉ đăng ký vượt quá giới hạn cho phép', Response::HTTP_BAD_REQUEST);
            }

            if($courseClass->current_student_count >= $courseClass->max_student_count) {
                return $this->errorResponse('Lớp học đã đủ số lượng sinh viên', Response::HTTP_BAD_REQUEST);
            }

            // Check prerequisites
            $prerequisites = CoursePrerequisite::where('course_id', $courseClass->course_id)->get();

            foreach ($prerequisites as $prerequisite) {
                dd($prerequisite);
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
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse(null, 'Đăng ký học phần thành công!');
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
        DB::beginTransaction();
        try {
            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
            $studentId = $student->student_id;
            $courseId = (int) $id;
            $courseClass = CourseClass::find($courseId);

            if($courseClass->status != COURSE_CLASS_STATUS_OPEN) {
                return $this->errorResponse('Lớp học phần đã đóng đăng ký không thể hủy', Response::HTTP_BAD_REQUEST);
            }

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

                if ($courseClass) {
                    $courseClass->decrement('current_student_count');
                }
            } else {
                return $this->errorResponse(null, 'Học phần không tồn tại hoặc đã bị hủy bỏ');
            }
            StudentCourseResult::where('student_id', $studentId)
                ->where('course_class_id', $courseId)
                ->delete();
            DB::commit();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

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
                "&requestType=payWithATM";

            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            // Prepare request payload
            $data = [
                'partnerCode' => $partnerCode,
                'partnerName' => "Test",
                'storeId' => "MomoTestStore",
                'requestType' => 'payWithATM',
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

    public function createVnpayPayment(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'amount' => 'required|numeric|min:10000',
                'orderInfo' => 'required|string|max:255',
            ]);

            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
            $studentId = $student->student_id;

            // VNPAY API credentials
            $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
            $vnp_Returnurl = "http://localhost:3000/student/payment";
            $vnp_IpnUrl = "https://b737-183-81-11-45.ngrok-free.app/api/payment/momo/callback";
            $vnp_TmnCode = "79FI7144";
            $vnp_HashSecret = "SA7APIVI4UVUDQRZHZRB8C9N8MRXN7HM";

            // Payment details
            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $request->input('amount') * 100,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $request->ip(),
                "vnp_Locale" => 'vn',
                "vnp_OrderInfo" => $request->input('orderInfo'),
                "vnp_OrderType" => 'billpayment',
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_IpnUrl" => $vnp_IpnUrl,
                "vnp_TxnRef" => time() . '',
                "vnp_Bill_Mobile" => $student->phone ?? '',
                "vnp_Bill_Email" => $student->email ?? '',
                "vnp_Bill_FirstName" => $student->first_name ?? '',
                "vnp_Bill_LastName" => $student->last_name ?? '',
                "vnp_Bill_Address" => $student->address ?? '',
                "vnp_Bill_City" => $student->city ?? '',
                "vnp_Bill_Country" => 'Vietnam',
                "vnp_Inv_Customer" => "Student " . $studentId,
                "vnp_Inv_Type" => "I",
            ];

            // Remove empty values
            $inputData = array_filter($inputData, function($value) {
                return $value !== null && $value !== '';
            });

            // Sort by key
            ksort($inputData);

            // Create hash data
            $hashData = http_build_query($inputData);
            $vnpSecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            // Build payment URL
            $vnp_Url .= '?' . $hashData . '&vnp_SecureHash=' . $vnpSecureHash;

            return $this->successResponse(['payUrl' => $vnp_Url], 'Payment URL generated successfully!');

        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    public function handleVnpayCallback(Request $request)
    {
        try {
            Log::info('VNPAY Callback Data:', $request->all());

            $vnp_HashSecret = "SA7APIVI4UVUDQRZHZRB8C9N8MRXN7HM"; // Must match create function

            $inputData = $request->all();
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

            // Remove unnecessary fields
            unset($inputData['vnp_SecureHash']);
            unset($inputData['vnp_SecureHashType']);

            // Filter and sort data
            $inputData = array_filter($inputData, function($value) {
                return $value !== null && $value !== '';
            });
            ksort($inputData);

            // Create hash data
            $hashData = '';
            foreach ($inputData as $key => $value) {
                if (strpos($key, 'vnp_') === 0) {
                    $hashData .= ($hashData == '' ? '' : '&') . $key . '=' . $value;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            if ($secureHash === $vnp_SecureHash) {
                $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
                $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
                $vnp_Amount = ($inputData['vnp_Amount'] ?? 0) / 100;
                $studentId = str_replace('Student ', '', $inputData['vnp_Inv_Customer'] ?? '');

                if ($vnp_ResponseCode == '00' && $studentId) {
                    // Process payment
                    $student = Student::find($studentId);
                    if ($student) {
                        $student->wallet_balance += $vnp_Amount;
                        $student->save();
                    }

                    Payment::create([
                        'student_id' => $studentId,
                        'payment_date' => now(),
                        'amount' => $vnp_Amount,
                        'payment_method' => 3,
                        'status' => 1,
                        'transaction_id' => $vnp_TxnRef,
                    ]);

                    return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
                }
            }

            Log::error('VNPAY Invalid Signature', [
                'expected' => $vnp_SecureHash,
                'actual' => $secureHash,
                'data' => $inputData
            ]);
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);

        } catch (\Exception $e) {
            Log::error('VNPAY Callback Error: ' . $e->getMessage());
            return response()->json(['RspCode' => '99', 'Message' => $e->getMessage()]);
        }
    }

    public function handleVnpayReturn(Request $request)
    {
        try {
            $vnp_HashSecret = "SA7APIVI4UVUDQRZHZRB8C9N8MRXN7HM";
            $inputData = $request->all();

            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
            unset($inputData['vnp_SecureHash']);
            unset($inputData['vnp_SecureHashType']);

            // Filter and sort data
            $inputData = array_filter($inputData, function($value) {
                return $value !== null && $value !== '';
            });
            ksort($inputData);

            // Create hash data
            $hashData = '';
            foreach ($inputData as $key => $value) {
                if (strpos($key, 'vnp_') === 0) {
                    $hashData .= ($hashData == '' ? '' : '&') . $key . '=' . $value;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            if ($secureHash !== $vnp_SecureHash) {
                return redirect()->route('student.account')
                    ->with('error', 'Chữ ký không hợp lệ');
            }

            $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
            $studentId = str_replace('Student ', '', $inputData['vnp_Inv_Customer'] ?? '');

            if ($vnp_ResponseCode == '00') {
                return redirect()->route('student.account')
                    ->with('success', 'Thanh toán thành công! Số tiền đã được cộng vào ví của bạn.');
            } else {
                $errorMsg = $this->getVnpayErrorMessage($vnp_ResponseCode);
                return redirect()->route('student.account')
                    ->with('error', 'Thanh toán không thành công. ' . $errorMsg);
            }

        } catch (\Exception $e) {
            return redirect()->route('student.account')
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    private function getVnpayErrorMessage($responseCode)
    {
        $errors = [
            '00' => 'Giao dịch thành công',
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP). Xin quý khách vui lòng thực hiện lại giao dịch.',
            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê)'
        ];

        return $errors[$responseCode] ?? 'Mã lỗi: ' . $responseCode;
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
