<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends BaseModel
{
    protected $primaryKey = 'student_id';
    protected $table = 'students';
    const TABLE_NAME = 'students';

    protected $fillable = [
        'student_id',
        'user_id',
        'student_code',
        'avatar',
        'phone_number',
        'status',
        'address',
        'birth_date',
        'admission_year',
        'gender',
        'class_id',
        'created_at',
        'updated_at'
    ];

    protected static array $fields = [
        'id' => 'student_id',
        'userId' => 'user_id',
        'code' => 'student_code',
        'avatar' => 'avatar',
        'phoneNumber' => 'phone_number',
        'status' => 'status',
        'address' => 'address',
        'birthDate' => 'birth_date',
        'admissionYear' => 'admission_year',
        'gender' => 'gender',
        'classId' => 'class_id',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
