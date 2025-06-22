<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DepartmentExport implements FromCollection, WithHeadings, ShouldAutoSize
{

    /**
     * @return \Illuminate\Support\Collection
     */
    private Collection $collection;
    private array $headings = ['ID', 'Tên khoa', 'Mã khoa', 'Mô tả', 'Active']; // Tên các cột trong excel

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }
    public function collection()
    {
        return $this->collection;
    }
    public function headings(): array
    {
        return $this->headings;
    }
}
