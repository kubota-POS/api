<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Models\ShopModel;
use App\Validations\ShopValidator;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class ShopController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index() {

        try {
            $data = ShopModel::get()->first();
            $response = ApiResponse::Success($data, 'get shop info');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function create(Request $request) {
        $input = $request->only(['name', 'description', 'email', 'phone', 'address']);

        $validator = Validator::make($input, [
            "name" => 'required',
            "description" => 'required',
            "email" => 'required|email',
            'phone' => 'required|numeric',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $shop = ShopModel::get()->first();

        if($shop) {
            $response = ApiResponse::Unprocess('shop info is alredy exist');
            return response()->json($response['json'], $response['status']);
        }

        $newShop = new ShopModel;
        $newShop->name = $input['name'];
        $newShop->description = $input['description'];
        $newShop->phone = $input['phone'];
        $newShop->email = $input['email'];
        $newShop->address = $input['address'];

        try {
            $store = $newShop->save();
            $response = ApiResponse::Success($newShop, 'shop is created');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update(Request $request) {
        $id = $request->id;
        $input = $request->only(['name', 'description', 'email', 'phone', 'address']);

        $validator = Validator::make($input, [
            "email" => 'email',
            'phone' => 'numeric'
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $shop = ShopModel::find($id);

        if($shop) {
            $shop->name =  isset($input['name']) ? $input['name'] : $shop->name;
            $shop->description = isset($input['description']) ? $input['description'] : $shop->description;
            $shop->phone = isset($input['phone']) ? $input['phone'] : $shop->phone;
            $shop->email = isset($input['email']) ? $input['email'] : $shop->email;
            $shop->address = isset($input['address']) ? $input['address'] : $shop->address;

            try {
                $update = $shop->push();
                $response = ApiResponse::Success($shop, 'shop is updated');
                return response()->json($response['json'], $response['status']);
    
            } catch(QueryException $e) {
                $response = ApiResponse::Unknown('something was wrong');
                return response()->json($response['json'], $response['status']);
            }
        }

        $response = ApiResponse::NotFound('shop not found');
        return response()->json($response['json'], $response['status']);
    }
}
