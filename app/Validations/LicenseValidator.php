<?php

namespace App\Validations;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use App\HttpResponse\ApiResponse;
use \Carbon\Carbon;

class LicenseValidator {

    /**
    *   License key checking
    */
    public static function check($license) {

        if(count($license) === 0) {
           return ApiResponse::Success([],'license does not exist');
        }

        try {
            $decrypted = Crypt::decryptString($license[0]['token']);
            $decode_json = json_decode($decrypted);
            $plan = $decode_json->plan;

            if($plan->active === false) {
                return ApiResponse::UnProcess('license is not active');
            }

            $current = Carbon::now()->timestamp;
            $expired = Carbon::create($plan->expired_at)->timestamp;

            if($expired < $current) {
                return ApiResponse::UnProcess('licnese is expired');
            }

            return ApiResponse::Success($license, 'license is active');

        } catch(DecryptException $e) {
            return ApiResponse::BedRequest('Invalid License Token');
        }
    }

    /**
    *   Validation for license activation and generate token
    */
    public static function activate($input) {
        $user = $input['user'];
        $plan = $input['plan'];

        if(!isset($user['first_name']) || !isset($user['last_name']) || !isset($user['display_name']) || !isset($user['address']) || !isset($user['phone']) || !isset($user['email'])) {
            return ApiResponse::BedRequest('Invalid user information');
        }

        if(!isset($plan['activated_at']) || !isset($plan['device']) || !isset($plan['duration'])) {
            return ApiResponse::BedRequest('Invalid plan');
        }

        $checkSerialKeyFormat = explode('-', $input['serial']);

        if(count($checkSerialKeyFormat) !== 6) {
            return ApiResponse::BedRequest('Invalid serial key format');
        }

        $input['plan']['active'] = true;
        $input['plan']['expired_at'] = Carbon::create($plan['activated_at'])->addYear($plan['duration'])->format('Y-m-d');

        $json_string = json_encode($input);
        $data = [
            'register' => $input,
            'encrypt_code' => Crypt::encryptString($json_string),
        ];

        return ApiResponse::Success($data, 'token is available');
    }

    /**
    *   Validation for license info [serial and token] save
    */
    public static function store($input) {
        try {
            $decrypted = Crypt::decryptString($input['key']);
            $decode_json = json_decode($decrypted);
            return ApiResponse::Success($decode_json, 'token is available');

        } catch(DecryptException $e) {
            return ApiResponse::BedRequest('Invalid License Token');
        }

    }
}