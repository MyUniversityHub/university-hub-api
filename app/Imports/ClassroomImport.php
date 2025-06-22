<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Statistic;
use App\Repositories\Contracts\ClassroomRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClassroomImport implements WithHeadingRow, ToCollection, WithValidation
{
    protected ClassroomRepositoryInterface $classroomRepository;

    public function __construct(ClassroomRepositoryInterface $classroomRepository)
    {
        $this->classroomRepository = $classroomRepository;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                $roomName = strtolower($row['room_name']);
                $exists = Classroom::whereRaw('LOWER(room_name) = ?', [$roomName])->exists();

                if ($exists) {
                    DB::rollBack();
                    continue;
                }

                $data = [
                    'room_name' => $row['room_name'],
                    'active' => $row['active'] ?? 1,
                ];
                $response = $this->classroomRepository->create($data);
                Statistic::where('name', 'total_classrooms')->increment('value');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            'room_name' => ['required', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'room_name.required' => 'Thiếu tên phòng học.',
        ];
    }
}
