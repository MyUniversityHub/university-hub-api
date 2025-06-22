<?php

namespace App\Imports;

use App\Models\Classes;
use App\Models\Statistic;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ClassesImport implements WithHeadingRow, ToCollection, WithValidation
{
    protected $classesRepository;

    public function __construct($classesRepository)
    {
        $this->classesRepository = $classesRepository;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            DB::beginTransaction();
            try {

                $data = [
                    Classes::field('majorId') => $row[Classes::field('majorId')],
                    Classes::field('courseYear') => $row[Classes::field('courseYear')] ?? null,
                    Classes::field('advisorName') => $row[Classes::field('advisorName')] ?? null,
                ];
                $class = $this->classesRepository->create($data);
                $class->{Classes::field('name')} = 'CLASS' . str_pad($class->{Classes::field('id')}, 4, '0', STR_PAD_LEFT);
                $class->save();
                Statistic::where('name', 'total_classes')->increment('value');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // Có thể log lỗi nếu cần
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            Classes::field('majorId') => ['required'],
            Classes::field('courseYear') => ['required', 'string'],
            Classes::field('advisorName') => ['required', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            Classes::field('majorId') . '.required' => 'Thiếu ngành.',
            Classes::field('courseYear') . '.required' => 'Thiếu niên khóa.',
            Classes::field('advisorName') . '.required' => 'Thiếu tên cố vấn học tập.',
        ];
    }
}
