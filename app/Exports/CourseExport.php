<?php

namespace App\Exports;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CourseExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private Collection $collection;
    private array $headings = ['ID', 'Mã học phần', 'Tên học phần', 'Số tín chỉ', 'Active', 'Ngày tạo', 'Ngày cập nhật'];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection->map(function ($item) {
            return [
                $item->{Course::field('id')},
                $item->{Course::field('code')},
                $item->{Course::field('name')},
                $item->{Course::field('credit_hours')},
                $item->{Course::field('active')},
                $item->{Course::field('createdAt')} ? $item->created_at->format('H:i:s d-m-Y') : null,
                $item->{Course::field('updatedAt')} ? $item->updated_at->format('H:i:s d-m-Y') : null,
            ];
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
