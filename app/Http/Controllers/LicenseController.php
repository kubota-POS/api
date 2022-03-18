<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use \Carbon\Carbon;
use App\Models\LicenseModel;
use App\Validations\LicenseValidator;
use App\HttpResponse\ApiResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Database\QueryException;

class LicenseController extends Controller
{
    public function __construct() {
        $this->middleware('license', ['except' => ['checkLicense', 'activate', 'saveToken']]);
        $this->middleware('jwt.verify', ['except' => ['checkLicense', 'activate', 'saveToken']]);
        // $this->middleware('device', ['except' => ['checkLicense', 'activate', 'saveToken']]);
    }

    /**
    *   Check license exit or not
    */
    public function checkLicense() {
        try {
            $license = LicenseModel::get()->first();
            $check = LicenseValidator::check($license);
            return response()->json($check['json'], $check['status']);
        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    /**
    *   Generate license token 
    */
    public function activate(Request $request) {
        $input = $request->only([
            'serial_key', 'first_name', 'last_name', 'email', 'phone', 'address', 'num_device', 'duration', 'activation_date' 
        ]);

        $validator = Validator::make($input, [
            'serial_key' => 'required|min:29|max:29',
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'email' => 'required|string|email',
            'phone' => 'required|string|max:11',
            'address' => 'required|string',
            'num_device' => 'required|numeric',
            'duration' => 'required|numeric',
            'activation_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $expired_date = new Carbon($input['activation_date']);
        $input['expired_date'] = $expired_date->addYears($input['duration'])->format('Y-m-d');
        $input['active'] = true;

        $encode_json = json_encode($input);
        $scretKey = substr(strtoupper(hash('sha256', $encode_json)), 0, 32);
        $scretKey = "base64:K/dyGPwXkel+tOBJS7yLmj61loDFlB7ZmtAr1hdrszk=";
        // dd([
        //     'app_key' => env('APP_KEY'),
        //     'scret_key' => $scretKey
        // ]);
            
        if(env('APP_KEY') !== $scretKey) {
            $response = ApiResponse::BedRequest('Invalid license key');
            return response()->json($response['json'], $response['status']);
        }

        $json_string = json_encode($input);

        try {
            $input['license_token'] = Crypt::encrypt($json_string);
            $input['secret_key'] = $scretKey;

            $response = ApiResponse::Success($input, 'license is available');
            return response()->json($response['json'], $response['status']);
            
        } catch(EncryptException $e) {
            $response = ApiResponse::Unknown('license encrypt error');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function saveToken(Request $request) {
        $input = $request->only(['serial', 'token']);

        $validator = Validator::make($input, [
            "serial" => "required",
            "token" => "required"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $licnese = LicenseModel::get()->first();

        if($licnese) {
            $response = ApiResponse::BedRequest('license key is already exist');
            return response()->json($response['json'], $response['status']); 
        }

        try {
            $store = LicenseModel::create($input);
            $response = ApiResponse::Success($input, 'license is created');
            return response()->json($response['json'], $response['status']);
        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function device(Request $request) {
        $license = $request->header('license');

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

            $response = ApiResponse::Success($licenseObject,'get license info');
            return response()->json($response['json'], $response['status']);

        } catch(Exception $e) {
            throw new Exception('Invalid License');
        }
    }
}
