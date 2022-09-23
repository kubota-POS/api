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

            return $this->Success($devices, 'get device list');

        } catch(QueryException $e) {
            return $this->Unknown('someting was wrong');
        }
    }

    public function first() {
        try {
            $device = DeviceModel::get()->first();

            if($device) {
                return $this->Success($device, 'first device is found');
            } else {
                return $this->Success([], 'first device is not found');
            }

        } catch(QueryException $e) {
            return $this->Unknown('something was wrong');
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
            return $this->BedRequest($validator->errors()->first());
        }

        $newDevice = new DeviceModel;

        $newDevice->name =$input['name'];
        $newDevice->ip = $input['ip'];
        $newDevice->mac = $input['mac'];
        $newDevice->note = isset($input['note']) ? $input['note'] : null;
        $newDevice->active = true;

        try {
            $store = $newDevice->save();
            return $this->Success($input, 'first device is created');

        } catch(QueryException $e) {
            return $this->Unknown('someting was wrong');
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
            return $this->BedRequest($validator->errors()->first());
        }

        $newDevice = new DeviceModel;

        $newDevice->name =$input['name'];
        $newDevice->ip = $input['ip'];
        $newDevice->mac = $input['mac'];
        $newDevice->note = isset($input['note']) ? $input['note'] : null;

        try {
            $store = $newDevice->save();
            return $this->Success($input, 'device is created');

        } catch(QueryException $e) {
            return $this->Unknown('someting was wrong');
        }
    }

    public function update(Request $request) {
        $input = $request->only(['name', 'ip', 'mac', 'note', 'active']);

        $validator = Validator::make($input, [
            "ip" => "unique:device",
            "mac" => "unique:device"
        ]);
        
        if ($validator->fails()) {
            return $this->BedRequest($validator->errors()->first());
        }

        try {
            $device = DeviceModel::find($request->id);

            if(!$device) {
                return $this->NotFound('device not found');
            }

            $update = DeviceModel::where('id', '=', $request->id)->update($input);

            if($update) {
                return $this->Success($input, 'device is updated');
            }
            return $this->Unprocess($input, 'update failed');

        } catch (QueryException $e) {
            return $this->Unknown('someting was wrong');
        }
    }
}
