<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \Carbon\Carbon;
use Validator;

use App\Models\LicenseModel;
use App\Validations\LicenseValidator;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class LicenseController extends Controller
{
    public function __construct() {
        $this->middleware('license', [
            'except' => ['checkLicense', 'activate', 'saveToken']
        ]);
    }

    /**
    *   Check license exit or not
    */
    public function checkLicense() {
        try {
            $licnese = LicenseModel::get()->toArray();
            $check = LicenseValidator::check($licnese);
            return response()->json($check['json'], $check['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('unknown error');
            return response()->json($response['json'], $response['status']);
        }
    }

    /**
    *   Generate license token 
    */
    public function activate(Request $request) {
        $input = $request->only(['serial', 'user', 'plan']);

        $validator = Validator::make($input, [
            "serial" => 'required',
            "user" => 'required',
            "plan" => 'required',
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $response = LicenseValidator::activate($input);
        return response()->json($response['json'], $response['status']);
    }

    /**
    *   Save license token and serial key to database
    */
    public function saveToken(Request $request) {
        $input = $request->only(['key']);

        $validator = Validator::make($input, [
            "key" => "required"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $licnese = LicenseModel::get()->first();

        $response = LicenseValidator::store($input);
        $data = $response['json']['data'];

        if($response['status'] !== 200) {
            return response()->json($response['json'], $response['status']);
        }

        $newLicnese = new LicenseModel;
        $newLicnese->serial = $data->serial;
        $newLicnese->token = $input['key'];
        
        try {
            $store = $newLicnese->save();
            $data->token = $input['key'];
            $response = ApiResponse::Success($data, 'liciense is created');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::UnProcess('serial number is already exist');
            return response()->json($response['json'], $response['status']);
        }
    }
}
