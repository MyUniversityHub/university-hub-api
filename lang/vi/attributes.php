<?php

use App\Models\Classes;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Department;
use App\Models\Major;
use App\Models\User;

return [
    'departments' => [
        Department::field('code') => 'Mã khoa',
        Department::field('name') => 'Tên khoa',
        Department::field('description') => 'Mô tả',
        Department::field('active') => 'Trạng thái',
    ],
    'majors' => [
        Major::field('code') => 'Mã chuyên ngành',
        Major::field('name') => 'Tên chuyên ngành',
        Major::field('departmentId') => 'Mã khoa',
        Major::field('active') => 'Trạng thái',
    ],
    'classes' => [
        Classes::field('name') => 'Tên lớp',
        Classes::field('courseYear') => 'Năm học',
        Classes::field('advisorName') => 'Tên giảng viên chủ nhiệm',
    ],
    'users' => [
        'name' => 'Tên người dùng',
        'user_name' => 'Tên tài khoản',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'role_id' => "Quyền",
    ],
    'courses' => [
        Course::field('code') => 'Mã môn học',
        Course::field('name') => 'Tên môn học',
        Course::field('credit_hours') => 'Số tín chỉ',
        Course::field('active') => 'Trạng thái',
    ],
    'classrooms' => [
        Classroom::field('name') => 'Tên phòng học',
        Classroom::field('description') => 'Mô tả',
        Classroom::field('active') => 'Trạng thái',
    ],
    'course_classes' => [
        CourseClass::field('course_id') => 'Mã môn học',
        CourseClass::field('classroom_id') => 'Mã phòng học',
        CourseClass::field('teacher_id') => 'Mã giảng viên',
        CourseClass::field('weekday') => 'Thứ trong tuần',
        CourseClass::field('semester') => 'Học kỳ',
        CourseClass::field('slot') => 'Số lượng sinh viên',
        CourseClass::field('lesson_start') => 'Tiết bắt đầu',
        CourseClass::field('lesson_end') => 'Tiết kết thúc',
        CourseClass::field('start_date') => 'Ngày bắt đầu',
        CourseClass::field('end_date') => 'Ngày kết thúc',
    ],
];
