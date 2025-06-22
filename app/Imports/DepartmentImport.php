<?php

namespace App\Imports;

use App\Models\Department;
use App\Models\Statistic;
use App\Repositories\Contracts\DepartmentRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class DepartmentImport implements WithHeadingRow, ToCollection, WithValidation
{
    protected DepartmentRepositoryInterface $departmentRepository;

    public function __construct(DepartmentRepositoryInterface $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                $departmentName = strtolower($row['department_name']);
                $exists = Department::whereRaw('LOWER(department_name) = ?', [$departmentName])->exists();

                if ($exists) {
                    DB::rollBack();
                    continue;
                }

                $data = [
                    'department_name' => $row['department_name'],
                    'description' => $row['description'],
                ];
                $response = $this->departmentRepository->create($data);
                $response->{Department::field('code')} = 'DE' . str_pad($response->{Department::field('id')}, 3, '0', STR_PAD_LEFT);
                $response->save();
                Statistic::where('name', 'total_departments')->increment('value');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // Có thể log lỗi hoặc bỏ qua dòng lỗi
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            'department_name' => ['required', 'string'],
            'description' => ['required', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'department_name.required' => 'Thiếu tiêu đề Tên khoa.',
            'description.required' => 'Thiếu tiêu đề Mô tả.',
        ];
    }
}
