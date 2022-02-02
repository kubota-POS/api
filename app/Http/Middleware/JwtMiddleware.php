<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\HttpResponse\ApiResponse;

class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if(!$user) throw new Exception('User Not Found');

        } catch(Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                $response = ApiResponse::Unauthorized('Token Invalid');
                return response()->json($response['json'],$response['status']);
            }

            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                $response = ApiResponse::Unauthorized('Token Expired');
                return response()->json($response['json'],$response['status']);
            }

            if($e->getMessage() === 'User Not Found') {
                $response = ApiResponse::NotFound('User not found');
                return response()->json($response['json'],$response['status']);
            }

            $response = ApiResponse::UnProcess('Authorization Token not found');
            return response()->json($response['json'],$response['status']);
        }

        return $next($request);
    }
}
