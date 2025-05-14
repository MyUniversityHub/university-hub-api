<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends BaseModel
{
    use SoftDeletes;
    protected $table = 'departments';
    protected $primaryKey = 'department_id';
    const TABLE_NAME = 'departments';
    protected $fillable = [
        'department_id',
        'department_code',
        'department_name',
        'description',
        'active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'department_id',
        'code' => 'department_code',
        'name' => 'department_name',
        'description' => 'description',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];
}
