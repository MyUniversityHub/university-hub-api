<?php

const LOGIN_RATE_LIMITED = 5; // giới hạn số lần đăng nhập sai

//MIDDLEWARE
const ROLE_PERMISSION_MIDDLEWARE = 'auth.role';

// ROLES
const ROLE_STATUS_ACTIVE = 1;  // trạng thái của role - đang active
const ROLE_STATUS_DEACTIVATE = 0; // trạng thái của role - chưa active

// MODULE ROLES
const MODULE_STUDENT = "STUDENT";
const MODULE_TEACHER = "TEACHER";
const MODULE_STAFF = "STAFF";
const MODULE_ADMIN = "ADMIN";
