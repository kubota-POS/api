<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use \Carbon\Carbon;

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
                return response()->json([
                    'success' => false,
                    'message' => 'Token Invalid',
                    'date' => Carbon::now()->format('Y-m-d H:i:s')
                ], 401);
            }

            if($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token Expired',
                    'date' => Carbon::now()->format('Y-m-d H:i:s')
                ], 422);
            }

            if($e->getMessage() === 'User Not Found') {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                    'date' => Carbon::now()->format('Y-m-d H:i:s')
                ], 400);
            }

            return response()->json([
                'success' => false,
                'message' => 'Authorization Token not found',
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ], 422); 
        }

        return $next($request);
    }
}
