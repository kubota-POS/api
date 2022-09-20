<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * 201, 200 - Success, Create Response (Default Status Code - 200)
     * @param data
     * @param message
     * @param code
     * @return response
     */

    protected function success($data, $message, $code = 200)
    {
        return response()->json([
            'message' => $message,
            'status_code' => $code,
            'data' => $data
        ], $code);
    }

    /**
     * 401 - Unauthorized Response (Default message - Unauthorized)
     *@param data
     *@param message
     *@return response
     */

    protected function unauthorized($message = 'Unauthorized')
    {
        return response()->json([
            'message' => 'Unauthorized',
            'status_code' => 401,
            'data' => $message
        ], 401);
    }

    /**
     * 404 - Not Found Response (Default data - null)
     * @param data
     * @return response
     */
    protected function notFound($data = null)
    {
        return response()->json([
            'message' => 'Resource Not Found',
            'status_code' => 404,
            'data' => $data
        ], 404);
    }


    /**
     * 422 - Unprocess Response (Default data - null)
     * @param data
     * @param response
     */

    protected function unprocess($message, $data = null)
    {
        return response()->json([
            'message' => $message,
            'status_code' => 422,
            'data' => $data
        ], 422);
    }

    /**
     * 500 - Internal Server Response (Default message - Unknown Error!)
     * @param data
     * @return response
     */
    protected function unknown($data = null, $message = 'Unknow Error!')
    {
        return response()->json([
            'message' => $message,
            'status_code' => 500,
            'data' => $data
        ], 500);
    }
}
