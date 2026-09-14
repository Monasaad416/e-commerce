<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, ?string $message = null, int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message ?? __('general.request_sent_successfully'),
            'data' => $data,
        ], $statusCode);
    }

    public static function error(?string $message = null, int $statusCode = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message ?? __('general.an_error_occurred'),
            'data' => $data,
        ], $statusCode);
    }
}
