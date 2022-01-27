<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Illuminate\Support\Facades\Crypt;
use \Carbon\Carbon;

class LicenseMiddleware
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
            $license = $request->header('license');

            $licenseObject = [
                "serial" => "A03D-114F-GN04-BBN4-6OB0-MB21",
                "userNum" => 10,
                "expired" => "2022-02-27",
                "activated" => true
            ];

            $ObjectToString = json_encode($licenseObject);
            $encrypt = Crypt::encryptString($ObjectToString);

            if(!$license) {
                throw new Exception('License Not Found');
            }

            try {
                $decryptLicense = Crypt::decryptString($license);
                dd($decryptLicense);
            } catch(Exception $e) {
                throw new Exception('Invalid License');
            }
            

        } catch(Exception $e) {
            
            if($e->getMessage() === 'License Not Found') {
                return response()->json([
                    'success' => false,
                    'message' => 'License not found',
                    'date' => Carbon::now()->format('Y-m-d H:i:s')
                ], 401); 
            }

            if($e->getMessage() === 'Invalid License') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid license key',
                    'date' => Carbon::now()->format('Y-m-d H:i:s')
                ], 401); 
            }
        }

        return $next($request);
    }
}
