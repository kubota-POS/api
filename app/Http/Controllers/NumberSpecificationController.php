<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\NumberSpecificationModel;
use App\Validations\LicenseValidator;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class NumberSpecificationController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index () {
        try {
            $numberSpecification = NumberSpecificationModel::get();
            $response = ApiResponse::Success($numberSpecification, 'get number specification list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function check() {
        try {
            $activeNumber = NumberSpecificationModel::where('active', true)->get();

            if(count($activeNumber) === 0) {
                $response = ApiResponse::Success([], 'no record found');
            } else {
                $response = ApiResponse::Success($activeNumber, 'active number specification list');   
            }

            return response()->json($response['json'], $response['status']);
        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update(Request $request) {
        $id = $request->id;
        $input = $request->only(['set_char']);

        $validator = Validator::make($input, [
            "set_char" => 'required|unique:number_specification|max:1|min:1|alpha'
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $input['set_char'] = strtoupper($input['set_char']);

        $setNumber = NumberSpecificationModel::find($id);

        if($setNumber) {
            $setNumber->set_char = $input['set_char'];

            try {
                $update = $setNumber->push();
                $response = ApiResponse::Success($setNumber, 'number specification is updated');
                return response()->json($response['json'], $response['status']);
    
            } catch(QueryException $e) {
                $response = ApiResponse::Unknown('something was wrong');
                return response()->json($response['json'], $response['status']);
            }
        }

        $response = ApiResponse::NotFound('number specification not found');
        return response()->json($response['json'], $response['status']);
    }

    public function active(Request $request) {
        $id = $request->id;

        $setNumber = NumberSpecificationModel::find($id);

        if($setNumber) {
            $setNumber->active = !$setNumber->active;

            try {
                $update = $setNumber->push();
                $status = $setNumber->active === true ? 'enable' : 'disable';
                $response = ApiResponse::Success($setNumber, "number specification is $status");
                return response()->json($response['json'], $response['status']);
    
            } catch(QueryException $e) {
                $response = ApiResponse::Unknown('something was wrong');
                return response()->json($response['json'], $response['status']);
            }
        }

        $response = ApiResponse::NotFound('number specification not found');
        return response()->json($response['json'], $response['status']);
    }
}
