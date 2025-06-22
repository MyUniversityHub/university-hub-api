<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CurriculumProgram extends BaseModel
{

    protected $table = 'curriculum_programs';
    public $incrementing = false;
    public $timestamps = true;

    protected $fillable = [
        'major_id',
        'course_id',
        'semester',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected static array $fields = [
        'majorId' => 'major_id',
        'courseId' => 'course_id',
        'semester' => 'semester',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at'
    ];

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id', 'major_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
