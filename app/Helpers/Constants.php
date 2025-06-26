<?php

const LOGIN_RATE_LIMITED = 5; // giới hạn số lần đăng nhập sai

//MIDDLEWARE
const ROLE_PERMISSION_MIDDLEWARE = 'auth.role';

const ROLE_MIDDLEWARE = 'auth.byRole';

// ROLES
const ROLE_STATUS_ACTIVE = 1;  // trạng thái của role - đang active
const ROLE_STATUS_DEACTIVATE = 0; // trạng thái của role - chưa active

// MODULE ROLES
const MODULE_STUDENT = "STUDENT";
const MODULE_TEACHER = "TEACHER";
const MODULE_STAFF = "STAFF";
const MODULE_ADMIN = "ADMIN";

// ROLE

const ROLE_ADMIN = 1;
const ROLE_STUDENT = 2;
const ROLE_TEACHER = 3;

// PAGINATION
const LIST_LIMIT_PAGINATION = 20;

// DEPARTMENT
const DEPARTMENT_STATUS_ACTIVE = 1;
const DEPARTMENT_STATUS_DEACTIVATE = 0;

// MAJOR
const MAJOR_STATUS_ACTIVE = 1;
const MAJOR_STATUS_DEACTIVATE = 0;

// CLASS
const CLASS_STATUS_ACTIVE = 1;
const CLASS_STATUS_DEACTIVATE = 0;

// CLASSROOM
const CLASSROOM_STATUS_ACTIVE = 1;
const CLASSROOM_STATUS_DEACTIVATE = 0;

// COURSE
const COURSE_STATUS_ACTIVE = 1;
const COURSE_STATUS_DEACTIVATE = 0;

//COURSE CLASS
const COURSE_CLASS_STATUS_READY_TO_OPEN = 1;
const COURSE_CLASS_STATUS_WAITING_FOR_TEACHER_ASSIGNMENT = 0;
const COURSE_CLASS_STATUS_OPEN = 2; // trạng thái mở lớp học phần
const COURSE_CLASS_STATUS_CLOSE = 3; // trạng thái đóng lớp học phần

const COURSE_CLASS_STATUS_END = 4; // trạng thái kết thúc lớp học phần


//COURSE RESULT

const COURSE_RESULT_STATUS_IN_PROGRESS = 1; // Trạng thái đang học
const COURSE_RESULT_STATUS_COMPLETED = 2; // Trạng thái đã học
const COURSE_RESULT_STATUS_FAILED = 0; // Trạng thái trượt môn

