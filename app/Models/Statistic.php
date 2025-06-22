<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Statistic extends Model
{
    protected $table = 'statistics';
    protected $primaryKey = 'name'; // Khóa chính là name
    public $incrementing = false; // Vì khóa chính không phải auto-increment
    protected $keyType = 'string'; // Kiểu dữ liệu của khóa chính là string

    protected $fillable = [
        'name',
        'value',
    ];
}
