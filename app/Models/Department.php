<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends BaseModel
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    const TABLE_NAME = 'departments';
    protected $fillable = [
        'id',
        'code',
        'name',
        'description',
        'active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'id',
        'code' => 'code',
        'name' => 'name',
        'description' => 'description',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];
}
