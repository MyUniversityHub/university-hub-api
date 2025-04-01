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
