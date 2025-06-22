<?php

namespace App\Exports;

use App\Models\Major;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;


class MajorExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */
    private Collection $collection;
    private array $headings = ['ID', 'Mã ngành', 'Tên khoa', 'Active', 'Ngày tạo', 'Ngày cập nhật'];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }
    public function collection()
    {
        return $this->collection->map(function ($item) {
            return [
                $item->{Major::field('id')},
                $item->{Major::field('code')},
                $item->{Major::field('name')},
                $item->{Major::field('active')},
                $item->{Major::field('createdAt')} ? $item->created_at->format('H:i:s d-m-Y') : null,
                $item->{Major::field('updatedAt')} ? $item->updated_at->format('H:i:s d-m-Y') : null,
            ];
        });
    }
    public function headings(): array
    {
        return $this->headings;
    }
}
