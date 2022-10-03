<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Carbon\Carbon;
use App\Models\DeviceModel;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

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

            return $this->success($devices, 'get device list');

        } catch(QueryException $e) {
            return $this->unknown();
        }
    }

    public function first() {
        try {
            $device = DeviceModel::get()->first();

            if($device) {
                return $this->success($device, 'first device is found');
            } else {
                return $this->success([], 'first device is not found');
            }

        } catch(QueryException $e) {
            return $this->unknown();
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
            return $this->unprocess($validator->errors()->first());
        }

        $newDevice = new DeviceModel;

        $newDevice->name =$input['name'];
        $newDevice->ip = $input['ip'];
        $newDevice->mac = $input['mac'];
        $newDevice->note = isset($input['note']) ? $input['note'] : null;
        $newDevice->active = true;

        try {
            $store = $newDevice->save();
            return $this->success($input, 'first device is created');

        } catch(QueryException $e) {
            return $this->unknown('someting was wrong');
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
            return $this->unprocess($validator->errors()->first());
        }

        $newDevice = new DeviceModel;

        $newDevice->name =$input['name'];
        $newDevice->ip = $input['ip'];
        $newDevice->mac = $input['mac'];
        $newDevice->note = isset($input['note']) ? $input['note'] : null;

        try {
            $store = $newDevice->save();
            return $this->success($input, 'device is created');

        } catch(QueryException $e) {
            return $this->unknown();
        }
    }

    public function update(Request $request) {
        $input = $request->only(['name', 'ip', 'mac', 'note', 'active']);

        $validator = Validator::make($input, [
            "ip" => "unique:device",
            "mac" => "unique:device"
        ]);
        
        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }

        try {
            $device = DeviceModel::find($request->id);

            if(!$device) {
                return $this->notFound('device not found');
            }

            $update = DeviceModel::where('id', '=', $request->id)->update($input);

            if($update) {
                return $this->success($input, 'device is updated');
            }
            return $this->unprocess($input, 'update failed');

        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
}
