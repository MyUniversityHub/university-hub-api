<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Eloquent\StudentRepositoryImpl;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function __construct(
        public StudentRepositoryInterface $studentRepository
    )
    {

    }

    public function create()
    {

    }


}
