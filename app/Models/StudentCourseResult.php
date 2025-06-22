<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentCourseResult extends BaseModel
{

    protected $table = 'student_course_results';
    protected $primaryKey = null; // Composite primary key
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'student_id',
        'course_class_id',
        'frequent_score_1',
        'frequent_score_2',
        'frequent_score_3',
        'absent_sessions',
        'average_score',
        'note',
    ];
protected static array $fields = [
    'studentId' => 'student_id',
    'courseClassId' => 'course_class_id',
    'frequentScore1' => 'frequent_score_1',
    'frequentScore2' => 'frequent_score_2',
    'frequentScore3' => 'frequent_score_3',
    'absentSessions' => 'absent_sessions',
    'averageScore' => 'average_score',
    'note' => 'note',
    'createdAt' => 'created_at',
    'updatedAt' => 'updated_at',
    'deletedAt' => 'deleted_at',
];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function courseClass()
    {
        return $this->belongsTo(CourseClass::class, 'course_class_id');
    }
}
