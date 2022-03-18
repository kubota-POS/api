<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use Exception;
use \Carbon\Carbon;
use App\HttpResponse\ApiResponse;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class LicenseMiddleware {

    public function handle(Request $request, Closure $next)
    {

        try {
            $license = $request->header('license');

            if(!$license) {
                throw new Exception('License Not Found');
            }

            try {
                $decryptLicense = Crypt::decrypt($license);
                $licenseObject = json_decode($decryptLicense);

                if($licenseObject->active === false) {
                    throw new Exception('License Not Active');
                    return;
                }

                $current = Carbon::now()->timestamp;
                $expired = Carbon::create($licenseObject->expired_date)->timestamp;

                if($expired < $current) {
                    throw new Exception('Licnese Expired');
                    return;
                }

            } catch(Exception $e) {
                throw new Exception('Invalid License');
            }
            

        } catch(Exception $e) {
            
            if($e->getMessage() === 'License Not Found') {
                $response = ApiResponse::UnProcess('License not found');
                return response()->json($response['json'], $response['status']); 
            }

            if($e->getMessage() === 'Invalid License') {
                $response = ApiResponse::UnProcess('Invalid License');
                return response()->json($response['json'], $response['status']); 
            }

            if($e->getMessage() === 'License Expired') {
                $response = ApiResponse::UnProcess('License Expired');
                return response()->json($response['json'], $response['status']); 
            }

            if($e->getMessage() === 'License Not Active') {
                $response = ApiResponse::UnProcess('License Not Active');
                return response()->json($response['json'], $response['status']); 
            }
        }

        return $next($request);
    }
}
