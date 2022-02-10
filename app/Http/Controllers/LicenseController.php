<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Validator;
use \Carbon\Carbon;
use App\Models\LicenseModel;
use App\Validations\LicenseValidator;
use App\HttpResponse\ApiResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\QueryException;

class LicenseController extends Controller
{
    public function __construct() {
        $this->middleware('license', ['except' => ['checkLicense', 'activate', 'saveToken']]);
        $this->middleware('jwt.verify', ['except' => ['checkLicense', 'activate', 'saveToken']]);
        $this->middleware('device', ['except' => ['checkLicense', 'activate', 'saveToken']]);
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
            $response = ApiResponse::Unknown('someting was wrong');
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

        if($licnese) {
            $response = ApiResponse::BedRequest('license key is already exist');
            return response()->json($response['json'], $response['status']); 
        }

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
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function device(Request $request) {
        $license = $request->header('license');

        try {
            $decryptLicense = Crypt::decryptString($license);
            $licenseObject = json_decode($decryptLicense);

            $plan = $licenseObject->plan;

            if($plan->active === false) {
                throw new Exception('License Not Active');
                return;
            }

            $current = Carbon::now()->timestamp;
            $expired = Carbon::create($plan->expired_at)->timestamp;

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
