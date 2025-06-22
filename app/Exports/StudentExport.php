<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StudentExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private Collection $collection;
    private array $headings = [
       'ID', 'Họ tên', 'Tên tài khoản', 'Email', 'Active', 'Ngày tạo', 'Ngày cập nhật'
    ];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection->map(function ($item) {
            return [
                $item->id,
                $item->name,
                $item->user_name,
                $item->email,
                $item->active,
                $item->created_at ? $item->created_at->format('H:i:s d-m-Y') : null,
                $item->updated_at ? $item->updated_at->format('H:i:s d-m-Y') : null,
            ];
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
