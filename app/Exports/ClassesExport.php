<?php

namespace App\Exports;

use App\Models\Classes;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassesExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private Collection $collection;
    private array $headings = ['ID', 'Mã lớp', 'Tên lớp', 'Mã chuyên ngành', 'Năm học', 'Chủ nhiệm', 'Active', 'Ngày tạo', 'Ngày cập nhật'];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection->map(function ($item) {
            return [
                $item->{Classes::field('id')},
                $item->{Classes::field('name')},
                $item->{Classes::field('majorId')},
                $item->{Classes::field('courseYear')},
                $item->{Classes::field('advisorName')},
                $item->{Classes::field('active')},
                $item->{Classes::field('createdAt')} ? $item->created_at->format('H:i:s d-m-Y') : null,
                $item->{Classes::field('updatedAt')} ? $item->updated_at->format('H:i:s d-m-Y') : null,
            ];
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
