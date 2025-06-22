<?php

namespace App\Http\Controllers;

use App\Models\RegistrationFeeDetail;
use App\Repositories\Contracts\RegistrationFeeDetailRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationFeeDetailController extends Controller
{
    use ApiResponse;

    public function __construct(
        public RegistrationFeeDetailRepositoryInterface $registrationFeeDetailRepository,
        public StudentRepositoryInterface $studentRepository
    ) {}

    public function index(Request $request)
    {
        try {
            $details = $this->registrationFeeDetailRepository->listWithFilter($request)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($details, 'Danh sách chi tiết phí đăng ký');
    }

    public function getRegistrationFeeDetailByStatus($status)
    {
        try {
            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
            $studentId = $student->student_id;
            $details = $this->registrationFeeDetailRepository->getRegistrationFeeDetailByStudentAndStatus($studentId, $status);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($details, 'Danh sách chi tiết phí đăng ký');
    }

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $response = $this->registrationFeeDetailRepository->create($data);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Thêm mới chi tiết phí đăng ký thành công!');
    }

    public function update(Request $request, $studentId, $courseClassId, $feeCode)
    {
        try {
            $data = $request->all();
            $response = $this->registrationFeeDetailRepository->updateCompositeKey(
                ['student_id' => $studentId, 'course_class_id' => $courseClassId, 'fee_code' => $feeCode],
                $data
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($response, 'Cập nhật chi tiết phí đăng ký thành công!');
    }

    public function destroy($studentId, $courseClassId, $feeCode)
    {
        try {
            $this->registrationFeeDetailRepository->deleteCompositeKey(['student_id' => $studentId, 'course_class_id' => $courseClassId, 'fee_code' => $feeCode]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse(null, 'Xóa chi tiết phí đăng ký thành công!');
    }

    public function payTuitionFee(Request $request)
    {
        try {
            $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
            $studentId = $student->student_id;
            $feeIds = $request->input('ids', []);

            if (empty($feeIds)) {
                return $this->errorResponse('Danh sách ID không được để trống', Response::HTTP_BAD_REQUEST);
            }

            dd($studentId, $feeIds);
            // Retrieve the fee details
            $feeDetails = RegistrationFeeDetail::where('student_id', $studentId)
                ->whereIn('course_class_id', $feeIds)
                ->get();

            dd($feeDetails);

            if ($feeDetails->isEmpty()) {
                return $this->errorResponse('Học phí không tồn tại hoặc đã được thanh toán', Response::HTTP_BAD_REQUEST);
            }

            $totalAmount = $feeDetails->sum('total_amount');

            // Check if the student has enough balance
            if ($student->wallet_balance < $totalAmount) {
                return $this->errorResponse('Số dư ví không đủ để thanh toán học phí', Response::HTTP_BAD_REQUEST);
            }

            // Deduct the fee amount from the student's wallet and debt
            $student->wallet_balance -= $totalAmount;
            $student->course_fee_debt -= $totalAmount;
            $student->save();

            // Update the fee details status to paid
            foreach ($feeDetails as $feeDetail) {
                $feeDetail->status = 1; // Paid
                $feeDetail->updated_at = now();
                $feeDetail->save();
            }

            return $this->successResponse(null, 'Thanh toán học phí thành công!');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }
}
