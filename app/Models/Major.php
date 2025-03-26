<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Major extends BaseModel
{
    use SoftDeletes;
    protected $table = 'majors';
    protected $primaryKey = 'id';
    const TABLE_NAME = 'majors';
    protected $fillable = [
        'id',
        'code',
        'name',
        'department_id',
        'active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'id',
        'code' => 'code',
        'name' => 'name',
        'departmentId' => 'department_id',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];
}
