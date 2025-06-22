<?php

namespace App\Exports;

use App\Models\Classroom;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClassroomExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private Collection $collection;
    private array $headings = ['ID', 'Tên phòng', 'Active', 'Ngày tạo', 'Ngày cập nhật'];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection->map(function ($item) {
            return [
                $item->{Classroom::field('id')},
                $item->{Classroom::field('name')},
                $item->{Classroom::field('active')},
                $item->{Classroom::field('createdAt')} ? $item->created_at->format('H:i:s d-m-Y') : null,
                $item->{Classroom::field('updatedAt')} ? $item->updated_at->format('H:i:s d-m-Y') : null,
            ];
        });
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
