<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use App\HttpResponse\ApiResponse;
use App\Models\DeviceModel;

class DeviceMiddleware
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
            $mac = $request->header('mac');
            $ip = $request->header('ip');

            if($mac === null || $mac === '') {
                throw new Exception('MAC address not found');
            }

            if($ip === null || $ip === '') {
                throw new Exception('IP address not found');
            }

            $devices = DeviceModel::get();
            $ips = [];
            $macs = [];

            foreach($devices as $device) {
                array_push($ips, $device->ip);
                array_push($macs, $device->mac);

                if($device->ip === $ip && $device->mac === $mac && $device->active === false) {
                    throw new Exception('access denied');
                    return;
                }
            }

            if(!in_array($ip, $ips)) {
                throw new Exception('Incorrect ip address');
                return;
            }

            if(!in_array($mac, $macs)) {
                throw new Exception('Incorrect mac address');
                return;
            }

        } catch(Exception $e) {
            
            if($e->getMessage() === 'MAC address not found') {
                $response = ApiResponse::UnProcess('MAC address not found');
                return response()->json($response['json'], $response['status']); 
            }

            if($e->getMessage() === 'IP address not found') {
                $response = ApiResponse::UnProcess('IP address not found');
                return response()->json($response['json'], $response['status']); 
            }

            if($e->getMessage() === 'Incorrect ip address') {
                $response = ApiResponse::UnProcess('Incorrect ip address');
                return response()->json($response['json'], $response['status']); 
            }

            if($e->getMessage() === 'Incorrect mac address') {
                $response = ApiResponse::UnProcess('Incorrect mac address');
                return response()->json($response['json'], $response['status']); 
            }

            if($e->getMessage() === 'access denied') {
                $response = ApiResponse::UnProcess('access denied');
                return response()->json($response['json'], $response['status']); 
            }
        }

        return $next($request);
    }
}
