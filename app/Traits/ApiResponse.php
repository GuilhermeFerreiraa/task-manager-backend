<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data, $message = 'Operation performed successfully', $statusCode = 200)
    {
        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data
        ], $statusCode); 
    }

    protected function errorResponse($message, $statusCode = 400)
    {
        return response()->json([
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => null
        ], $statusCode); 
    }
}
