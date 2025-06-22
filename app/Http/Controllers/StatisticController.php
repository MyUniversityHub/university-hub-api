<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\StatisticRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StatisticController extends Controller
{
    use ApiResponse;

    public function __construct(
        public StatisticRepositoryInterface $statisticRepository
    ) {}

    public function index(Request $request)
    {
        try {
            $data = $this->statisticRepository->listWithFilter($request)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }
        return $this->successResponse($data, 'Danh sách thống kê');
    }

//    public function show(Request $request)
//    {
//        $name = $request->get('name');
//        try {
//            $statistic = $this->statisticRepository->find($name);
//        } catch (\Exception $e) {
//            return $this->errorResponse('Error', Response::HTTP_NOT_FOUND, $e->getMessage());
//        }
//        return $this->successResponse($statistic, 'Chi tiết thống kê');
//    }

}
