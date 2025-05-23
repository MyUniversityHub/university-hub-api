<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends BaseModel
{
    use SoftDeletes;
    protected $table = 'majors';
    protected $primaryKey = 'major_id';
    const TABLE_NAME = 'majors';
    protected $fillable = [
        'major_id',
        'major_code',
        'major_name',
        'department_id',
        'active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'major_id',
        'code' => 'major_code',
        'name' => 'major_name',
        'departmentId' => 'department_id',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];
}
