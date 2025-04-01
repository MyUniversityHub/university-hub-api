<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classes extends BaseModel
{
    use SoftDeletes;

    protected $table = 'classes';
    protected $primaryKey = 'id';
    const TABLE_NAME = 'classes';

    protected $fillable = [
        'id',
        'name',
        'major_id',
        'course_year',
        'student_count',
        'active',
        'created_at',
        'updated_at',
        'deleted_at',
        'advisor_name'
    ];

    protected static array $fields = [
        'id' => 'id',
        'name' => 'name',
        'majorId' => 'major_id',
        'courseYear' => 'course_year',
        'studentCount' => 'student_count',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at',
        'advisorName' => 'advisor_name'
    ];


}
