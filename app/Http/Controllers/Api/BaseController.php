<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller
{
    /**
     * Helper method to return a success response.
     */
    protected function successResponse(string $message, $res = null, int $status): JsonResponse
    {
        $response = ['message' => $message];
        if (!is_null($res)) {
            $response['response'] = $res;
        }
        return response()->json($response, $status);
    }

    /**
     * Helper method to return an error response.
     */
    protected function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }

}
