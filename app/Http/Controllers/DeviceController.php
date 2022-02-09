<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use \Carbon\Carbon;
use App\Models\DeviceModel;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class DeviceController extends Controller
{
    public function __construct() {
        $this->middleware('license', ['except' => ['first', 'firstDeviceCreate']]);
        $this->middleware('jwt.verify', ['except' => ['first', 'firstDeviceCreate']]);
    }

    public function first() {
        try {
            $device = DeviceModel::get()->first();

            if($device) {
                $response = ApiResponse::Success($device, 'first device is found');
                return response()->json($response['json'], $response['status']);
            } else {
                $response = ApiResponse::Success([], 'first device is not found');
                return response()->json($response['json'], $response['status']);
            }

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function firstDeviceCreate(Request $request) {
        $input = $request->only(['name', 'ip', 'mac', 'note']);

        $validator = Validator::make($input, [
            "name" => "required|unique:device",
            "ip" => "required|unique:device",
            "mac" => "required|unique:device"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $newDevice = new DeviceModel;

        $newDevice->name =$input['name'];
        $newDevice->ip = $input['ip'];
        $newDevice->mac = $input['mac'];
        $newDevice->note = isset($input['note']) ? $input['note'] : null;
        $newDevice->active = true;

        try {
            $store = $newDevice->save();
            $response = ApiResponse::Success($input, 'first device is created');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
