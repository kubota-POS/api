<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \Carbon\Carbon;
use Validator;

use App\Models\LicenseModel;

class LicenseController extends Controller
{
    public function __construct() {
        $this->middleware('license', ['except' => ['checkLicense']]);
    }

    public function checkLicense() {
        $licnese = LicenseModel::get();

        return response()->json([
            'success' => true,
            'message' => 'success',
            'data' => [
                'license' => $licnese,
                'date' => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ], 200);
    }
}
