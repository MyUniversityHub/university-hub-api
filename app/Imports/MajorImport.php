<?php

namespace App\Imports;

use App\Models\Major;
use App\Models\Statistic;
use App\Repositories\Contracts\MajorRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MajorImport implements WithHeadingRow, ToCollection, WithValidation
{
    protected MajorRepositoryInterface $majorRepository;

    public function __construct(MajorRepositoryInterface $majorRepository)
    {
        $this->majorRepository = $majorRepository;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            DB::beginTransaction();
            try {
                $majorName = strtolower($row['major_name']);
                $exists = Major::whereRaw('LOWER(major_name) = ?', [$majorName])->exists();

                if ($exists) {
                    DB::rollBack();
                    continue;
                }

                $data = [
                    'major_name' => $row['major_name'],
                    'department_id' => $row['department_id'],
                ];
                $response = $this->majorRepository->create($data);
                $response->{Major::field('code')} = 'MA' . str_pad($response->{Major::field('id')}, 3, '0', STR_PAD_LEFT);
                $response->save();
                Statistic::where('name', 'total_majors')->increment('value');
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
            'major_name' => ['required', 'string'],
            'department_id' => ['required'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'major_name.required' => 'Thiếu tiêu đề Tên ngành.',
            'department_id.required' => 'Thiếu khoa.',
        ];
    }
}
