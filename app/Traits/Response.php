<?php

namespace App\Traits;

trait Response
{
    public $imageExtensions = ['jpg', 'png', 'jpeg'];

    public function success($data, $message, $statusCode)
    {
        return response()->json([
            'data' => $data,
            'status' => true,
            'message' => $message,
        ], $statusCode);
    }

    public function error($message, $statusCode)
    {
        return response()->json([
            'data' => null,
            'status' => false,
            'message' => $message,
        ], $statusCode);
    }
}
