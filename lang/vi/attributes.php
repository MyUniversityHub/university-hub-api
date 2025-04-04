<?php

use App\Models\Classes;
use App\Models\Department;
use App\Models\Major;
use App\Models\User;

return [
    'departments' => [
        Department::code() => 'Mã khoa',
        Department::name() => 'Tên khoa',
        Department::description() => 'Mô tả',
        Department::active() => 'Trạng thái',
    ],
    'majors' => [
        Major::code() => 'Mã chuyên ngành',
        Major::name() => 'Tên chuyên ngành',
        Major::departmentId() => 'Mã khoa',
        Major::active() => 'Trạng thái',
    ],
    'classes' => [
        Classes::name() => 'Tên lớp',
        Classes::courseYear() => 'Năm học',
        Classes::advisorName() => 'Tên giảng viên chủ nhiệm',
    ],
    'users' => [
        'name' => 'Tên người dùng',
        'user_name' => 'Tên tài khoản',
        'email' => 'Email',
        'password' => 'Mật khẩu',
        'role_id' => "Quyền",
    ],
];
