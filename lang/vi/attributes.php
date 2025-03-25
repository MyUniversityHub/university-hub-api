<?php

use App\Models\Department;

return [
    'departments' => [
        Department::code() => 'Mã khoa',
        Department::name() => 'Tên khoa',
        Department::description() => 'Mô tả',
        Department::active() => 'Trạng thái',
    ],
];
