<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistrationFeeDetail extends BaseModel
{
    use SoftDeletes;

    protected $table = 'registration_fee_details';
    protected $primaryKey = null; // Composite primary key
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'course_class_id',
        'fee_code',
        'fee_name',
        'credit_count',
        'unit_price',
        'total_amount',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static array $fields = [
        'studentId' => 'student_id',
        'courseClassId' => 'course_class_id',
        'feeCode' => 'fee_code',
        'feeName' => 'fee_name',
        'creditCount' => 'credit_count',
        'unitPrice' => 'unit_price',
        'totalAmount' => 'total_amount',
        'status' => 'status',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
        'deletedAt' => 'deleted_at',
    ];
}
