<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseClass extends BaseModel
{
    use SoftDeletes;

    protected $table = 'course_classes';
    protected $primaryKey = 'course_class_id';
    const TABLE_NAME = 'course_classes';

    protected $fillable = [
        'course_class_id',
        'course_class_code',
        'course_id',
        'classroom_id',
        'teacher_id',
        'weekdays',
        'semester',
        'lesson_start',
        'lesson_end',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'id' => 'course_class_id',
        'code' => 'course_class_code',
        'courseId' => 'course_id',
        'classroomId' => 'classroom_id',
        'teacherId' => 'teacher_id',
        'weekdays' => 'weekdays',
        'semester' => 'semester',
        'lessonStart' => 'lesson_start',
        'lessonEnd' => 'lesson_end',
        'startDate' => 'start_date',
        'endDate' => 'end_date',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];
}
