<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiException extends Exception
{
    public function __construct(
        string $message,
        protected int $status = 400,
        protected ?string $errorCode = null,
        protected mixed $data = null,
    ) {
        parent::__construct($message, $status);
    }

    public function render(Request $request): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $this->getMessage(),
        ];

        if ($this->errorCode !== null) {
            $payload['error_code'] = $this->errorCode;
        }

        if ($this->data !== null) {
            $payload['data'] = $this->data;
        }

        return response()->json($payload, $this->status);
    }
}
