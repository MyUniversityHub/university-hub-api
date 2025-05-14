<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends BaseModel
{
    use SoftDeletes;

    protected $table = 'courses';
    protected $primaryKey = 'course_id';
    const TABLE_NAME = 'courses';

    protected $fillable = [
        'course_id',
        'course_code',
        'course_name',
        'credit_hours',
        'active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'course_id',
        'code' => 'course_code',
        'name' => 'course_name',
        'creditHours' => 'credit_hours',
        'active' => 'active',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];

    public function prerequisites()
    {
        return $this->hasMany(CoursePrerequisite::class, 'course_id', 'course_id');
    }
}
