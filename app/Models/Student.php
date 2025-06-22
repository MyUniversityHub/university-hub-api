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

    public function getCurrentSemester()
    {
        // Tính toán semester hiện tại dựa trên admission_year
        $currentYear = date('Y');
        $currentMonth = date('m');

        // Giả sử năm học chia làm 2 học kỳ:
        // - Học kỳ 1: từ tháng 8 năm trước đến tháng 1 năm sau
        // - Học kỳ 2: từ tháng 2 đến tháng 7
        $semester = ($currentMonth >= 8 || $currentMonth <= 1) ? 1 : 2;

        // Tính năm học (nếu đang trong học kỳ 1 thì năm học là năm hiện tại, học kỳ 2 thì năm học là năm hiện tại -1)
        $academicYear = $semester === 1 ? $currentYear : $currentYear - 1;

        // Tính số học kỳ đã trải qua (mỗi năm có 2 học kỳ)
        $yearsSinceAdmission = $academicYear - $this->{Student::field('admissionYear')};
        $currentSemester = $yearsSinceAdmission * 2 + $semester;

        return $currentSemester;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'class_id');
    }
}
