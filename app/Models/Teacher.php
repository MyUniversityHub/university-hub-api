<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends BaseModel
{
    protected $table = 'teachers';
    protected $primaryKey = 'id';
    const TABLE_NAME = 'teachers';
    protected $fillable = [
        'teacher_id',
        'user_id',
        'teacher_code',
        'avatar',
        'address',
        'birth_date',
        'gender',
        'department_id',
        'degree',
        'specialization',
        'phone',
        'email',
        'active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'teacher_id',
        'userId' => 'user_id',
        'code' => 'teacher_code',
        'avatar' => 'avatar',
        'address' => 'address',
        'birthDate' => 'birth_date',
        'gender' => 'gender',
        'departmentId' => 'department_id',
        'degree' => 'degree',
        'specialization' => 'specialization',
        'phone' => 'phone',
        'email' => 'email',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
