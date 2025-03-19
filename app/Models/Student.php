<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends BaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'students';


    protected $fillable = [
        'user_id',
        'student_code',
        'birth_date',
        'gender',
        'department_id',
        'class_id',
        'created_at',
        'updated_at'
    ];

    protected static array $fields = [
        'userId' => 'user_id',
        'studentCode' => 'student_code',
        'bod' => 'birth_date',
        'gender' => 'gender',
        'departmentId' => 'department_id',
        'classId' => 'class_id',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    ];
}
