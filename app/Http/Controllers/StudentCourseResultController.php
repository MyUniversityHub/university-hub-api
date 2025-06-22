<?php

namespace App\Http\Controllers;

use App\Models\StudentCourseResult;
use App\Repositories\Contracts\StudentCourseResultRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class StudentCourseResultController extends Controller
{
    use ApiResponse;

    public function __construct(
        public StudentCourseResultRepositoryInterface $studentCourseResultRepository,
        public StudentRepositoryInterface             $studentRepository,
        public UserRepositoryInterface $userRepository
    )
    {
    }

    public function index()
    {
        $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
        $studentId = $student->student_id;
        try {
            $results = $this->studentCourseResultRepository->getResultsOfStudent($studentId);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($results, 'Danh sách kết quả học tập');
    }

    public function destroy($studentId, $courseClassId)
    {
        try {
            $this->studentCourseResultRepository->deleteCompositeKey(['student_id' => $studentId, 'course_class_id' => $courseClassId]);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse(null, 'Xóa kết quả học tập thành công!');
    }

    public function getClassSchedule(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $student = $this->studentRepository->getStudentWithUserInfoById(auth()->user()->id);
        $studentId = $student->student_id;
        try {
            $schedules = $this->studentCourseResultRepository->getClassScheduleByStudentId($studentId, $startDate, $endDate);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($schedules, 'Lịch học của sinh viên');
    }

    public function getTeachingSchedule(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
        $teacherId = auth()->user()->id;
        try {
            $schedules = $this->studentCourseResultRepository->getClassScheduleByTeacherId(1, $startDate, $endDate);
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($schedules, 'Lịch dạy của giảng viên');
    }

    public function updateScoresForStudents(Request $request, $id)
    {
        $data = $request->all();

        DB::beginTransaction();
        try {
            foreach ($data['scores'] as $score) {
                // Tính điểm trung bình và grade
                $frequentScore1 = $score['frequent_score_1'] ?? null;
                $frequentScore2 = $score['frequent_score_2'] ?? null;
                $frequentScore3 = $score['frequent_score_3'] ?? null;
                $finalScore = $score['final_score'] ?? null;
                // Tính average_score nếu có đủ điểm thành phần
                $averageScore = $this->calculateAverageScore($frequentScore1, $frequentScore2, $frequentScore3, $finalScore);
                // Xác định grade dựa trên average_score
                $grade = $this->determineGrade($averageScore);

                $absentSessions = $score['absent_sessions'] ?? 0;
                $status = $this->determineStatus($grade, $absentSessions, $averageScore);

                // Lấy bản ghi hiện tại từ database (nếu có)
                $existingRecord = DB::table('student_course_results')
                    ->where('student_id', $score['student_id'])
                    ->where('course_class_id', $id)
                    ->first();

                // Kiểm tra xem có sự thay đổi nào không
                $hasChanges = !$existingRecord ||
                    ($existingRecord->frequent_score_1 != $frequentScore1) ||
                    ($existingRecord->frequent_score_2 != $frequentScore2) ||
                    ($existingRecord->frequent_score_3 != $frequentScore3) ||
                    ($existingRecord->final_score != $finalScore) ||
                    ($existingRecord->absent_sessions != ($score['absent_sessions'] ?? null)) ||
                    ($existingRecord->note != ($score['note'] ?? null)) ||
                    ($existingRecord->average_score != $averageScore) ||
                    ($existingRecord->grade != $grade) ||
                    ($existingRecord->status != $status);

                // Thực hiện cập nhật hoặc thêm mới
                DB::table('student_course_results')->updateOrInsert(
                    [
                        'student_id' => $score['student_id'],
                        'course_class_id' => $id,
                    ],
                    [
                        'frequent_score_1' => $frequentScore1,
                        'frequent_score_2' => $frequentScore2,
                        'frequent_score_3' => $frequentScore3,
                        'final_score' => $finalScore,
                        'absent_sessions' => $score['absent_sessions'] ?? null,
                        'average_score' => $averageScore,
                        'grade' => $grade,
                        'status' => $status,
                        'note' => $score['note'] ?? null,
                        'updated_at' => now(),
                    ]
                );

                $user = $this->studentRepository->find($score['student_id']);
                $userId = $user->user_id;
                // Chỉ gửi thông báo nếu có thay đổi
                if ($hasChanges) {
                    DB::table('notification')->insert([
                        'user_id' => $userId,
                        'title' => 'Cập nhật điểm số',
                        'message' => 'Điểm số của bạn đã được cập nhật!',
                        'status' => 0, // 0: Chưa đọc, 1: Đã đọc
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return $this->successResponse("", 'Cập nhật điểm thành công');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

    /**
     * Tính điểm trung bình dựa trên các thành phần điểm
     * Giả sử: điểm chuyên cần (15%), giữa kỳ 1 (15%), giữa kỳ 2 (15%), cuối kỳ (55%)
     */
    private function calculateAverageScore($frequentScore1, $frequentScore2, $frequentScore3, $finalScore)
    {
        // Nếu không có điểm cuối kỳ, không thể tính điểm trung bình
        if ($finalScore === null) {
            return null;
        }

        // Tính điểm trung bình với trọng số
        $sum = 0;
        $weight = 0;

        if ($frequentScore1 !== null) {
            $sum += $frequentScore1 * 0.15;
            $weight += 0.15;
        }

        if ($frequentScore2 !== null) {
            $sum += $frequentScore2 * 0.15;
            $weight += 0.15;
        }

        if ($frequentScore3 !== null) {
            $sum += $frequentScore3 * 0.15;
            $weight += 0.15;
        }

        if ($finalScore !== null) {
            $sum += $finalScore * 0.55;
            $weight += 0.55;
        }

        if ($weight === 0) {
            return null;
        }

        return round($sum / $weight, 2);
    }

    /**
     * Xác định grade dựa trên điểm trung bình
     * A: 8.5 - 10
     * B: 7.5 - < 8.5
     * C: 6.5 - 7.4
     * D: 5 - 6.4
     * F: < 5
     */
    private function determineGrade($averageScore)
    {
        if ($averageScore === null) {
            return null;
        }

        if ($averageScore >= 8.5) return 'A';
        if ($averageScore >= 8.0) return 'B+';
        if ($averageScore >= 7.0) return 'B';
        if ($averageScore >= 6.5) return 'C+';
        if ($averageScore >= 5.5) return 'C';
        if ($averageScore >= 5.0) return 'D+';
        if ($averageScore >= 4.0) return 'D';
        return 'F';
    }

    private function determineStatus($grade, $absentSessions, $averageScore)
    {
        // Trượt môn nếu điểm F hoặc vắng trên 10 buổi
        if ($grade === 'F' || $absentSessions > 10) {
            return 0;
        }

        // Đã học xong nếu có điểm trung bình hoặc điểm chữ
        if ($averageScore !== null || $grade !== null) {
            return 2;
        }

        // Mặc định là đang học
        return 1;
    }

    public function getCourseResultByStatus($status)
    {
        try {
            $user = $this->userRepository->find(auth()->user()->id);
            $studentId = $user->student->student_id ?? null;
            $courseResults = $this->studentCourseResultRepository->getCourseResultByStatus($studentId, $status);
            return $this->successResponse($courseResults, 'Danh sách kết quả học tập theo trạng thái');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
    }

}
