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
        // $this->middleware('device', ['except' => ['first', 'firstDeviceCreate']]);
    }

    public function index() {
        try {
            $devices = DeviceModel::get();

            $response = ApiResponse::Success($devices, 'get device list');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
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
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function firstDeviceCreate(Request $request) {
        $input = $request->only(['name', 'ip', 'mac', 'note']);

        $validator = Validator::make($input, [
            "name" => "required",
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

    public function create(Request $request) {
        $input = $request->only(['name', 'ip', 'mac', 'note']);

        $validator = Validator::make($input, [
            "name" => "required",
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

        try {
            $store = $newDevice->save();
            $response = ApiResponse::Success($input, 'device is created');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update(Request $request) {
        $input = $request->only(['name', 'ip', 'mac', 'note', 'active']);

        $validator = Validator::make($input, [
            "ip" => "unique:device",
            "mac" => "unique:device"
        ]);
        
        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $device = DeviceModel::find($request->id);

            if(!$device) {
                $response = ApiResponse::NotFound('device not found');
                return response()->json($response['json'], $response['status']);
            }

            $update = DeviceModel::where('id', '=', $request->id)->update($input);

            if($update) {
                $response = ApiResponse::Success($input, 'device is updated');
                return response()->json($response['json'], $response['status']);
            }

            $response = ApiResponse::Unprocess($input, 'update failed');
            return response()->json($response['json'], $response['status']);

        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);  
        }
    }
}
