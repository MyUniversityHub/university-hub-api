<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePrerequisite extends Model
{
    protected $table = 'course_prerequisites';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'course_id',
        'prerequisite_course_id',
        'type',
        'created_at',
        'updated_at'
    ];

    public static function field($key)
    {
        return match ($key) {
            'courseId' => 'course_id',
            'prerequisiteCourseId' => 'prerequisite_course_id',
            'type' => 'type',
            default => null,
        };
    }
}
