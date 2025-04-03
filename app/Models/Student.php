<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends BaseModel
{
    protected $primaryKey = 'id';
    protected $table = 'students';
    const TABLE_NAME = 'students';

    protected $fillable = [
        'id',
        'user_id',
        'student_code',
        'avatar',
        'phone_number',
        'status',
        'address',
        'birth_date',
        'gender',
        'class_id',
        'created_at',
        'updated_at'
    ];

    protected static array $fields = [
        'userId' => 'user_id',
        'code' => 'student_code',
        'avatar' => 'avatar',
        'phoneNumber' => 'phone_number',
        'status' => 'status',
        'address' => 'address',
        'bod' => 'birth_date',
        'gender' => 'gender',
        'classId' => 'class_id',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    ];
}
