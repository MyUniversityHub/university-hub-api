<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class PaymentController extends Controller
{
    use ApiResponse;

    protected $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function index(Request $request)
    {
        $from = $request->input('start_day');
        $to = $request->input('end_day');

        $query = Payment::query();

        if ($from && $to) {
            $from = Carbon::parse($from)->startOfDay();
            $to = Carbon::parse($to)->endOfDay();
            $query->whereBetween('payment_date', [$from, $to]);
        }

        $payments = $query->get();

        return $this->successResponse($payments, 'Danh sách thanh toán');
    }


}
