<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\Statistic;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Symfony\Component\HttpFoundation\Response;

class CourseImport implements WithHeadingRow, ToCollection, WithValidation
{
    use ApiResponse;
    protected CourseRepositoryInterface $courseRepository;

    public function __construct(CourseRepositoryInterface $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                $courseName = strtolower($row['course_name']);
                $exists = Course::whereRaw('LOWER(course_name) = ?', [$courseName])->exists();

                if ($exists) {
                    DB::rollBack();
                    continue;
                }

                // Parse prerequisites from string to array if needed
                $prerequisites = [];
                if (!empty($row['prerequisites'])) {
                    if (is_string($row['prerequisites'])) {
                        $prerequisites = json_decode($row['prerequisites'], true);
                        if (!is_array($prerequisites)) {
                            $prerequisites = [];
                        }
                    } elseif (is_array($row['prerequisites'])) {
                        $prerequisites = $row['prerequisites'];
                    }
                }

                $data = [
                    'course_name' => $row['course_name'],
                    'credit_hours' => $row['credit_hours'],
                    'prerequisites' => $prerequisites,
                ];

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
                throw new \Exception('Lỗi import môn học: ' . $e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
    }

    public function rules(): array
    {
        return [
            'course_name' => ['required', 'string'],
            'credit_hours' => ['required'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'course_name.required' => 'Thiếu tên học phần.',
            'credit_hours.required' => 'Thiếu số tín chỉ.',
        ];
    }
}
