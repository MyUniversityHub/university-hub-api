<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Success response
     */
    public function successResponse($data = [], $message = 'Success', $code = Response::HTTP_OK): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Error response
     */
    public function errorResponse($message = 'Error', $code = Response::HTTP_BAD_REQUEST, $errors = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Validation error response
     */
    public function validationErrorResponse($errors = [], $message = 'Validation Error', $code = Response::HTTP_UNPROCESSABLE_ENTITY): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
