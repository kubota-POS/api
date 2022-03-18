<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Validator;

class MerchantController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify', 'device']);
    }

    public function index()
    {
        try {
            $merchant = Merchant::get();
            $response = ApiResponse::Success($merchant, 'get merchant list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()-json($response['json'], $response['status']);
        }
    }

    public function create(Request $request)
    {
        $input = $request->only(['name', 'email', 'phone', 'address']);

        $validator = Validator::make($input, [
            "name" => 'required',
            "phone" => 'required|unique:merchants',
            "email" => 'unique:merchants'
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try{
            $merchant = Merchant::create($input);
            $response = ApiResponse::Success($merchant, 'get merchant list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e){
            $response = ApiResponse::Unknow('something was wrong');
            return response()->json($response['json'],$response['status']);
        }
    }

    public function detail(Request $request)
    {
        $id = $request->id;

        try {
            $merchant = Merchant::find($id);

            if(!$merchant){
                $response = ApiResponse::NotFound('Merchant is not found');
                return response()->json($response['json'], $response['status']);
            }

            $response = ApiResponse::Success($merchant, 'Merchant is retrived');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $merchant = Merchant::find($id);

            if(!$merchant){
                $response = ApiResponse::NotFound('merchant is not found');
                return response()->json($response['json'], $response['status']);
            }
        $input = $request->only(['name', 'phone', 'email', ' address']);

        $validator = Validator::make($input, [
            "phone" => 'unique:merchants',
            "email" => 'unique:merchants',
            "address" => 'unique:merchants'
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $update = Merchant::where('id', '=', $id)->update($input);
            $merchant->refresh();
            $response = ApiResponse::Success($merchant, 'get updated merchant');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        try {
            $merchant = Merchant::find($id);

            if(!$merchant){
                $response = ApiResponse::NotFound('merchant is not found');
                return response()->json($response['json'], $response['status']);
            }

            $merchant->delete();
            $response = ApiResponse::Success($merchant, 'merchant is deleted');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
