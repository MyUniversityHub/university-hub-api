<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classroom extends BaseModel
{
    use SoftDeletes;

    protected $table = 'classrooms';
    protected $primaryKey = 'classroom_id';
    const TABLE_NAME = 'classrooms';

    protected $fillable = [
        'classroom_id',
        'room_name',
        'active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'classroom_id',
        'name' => 'room_name',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];
}
