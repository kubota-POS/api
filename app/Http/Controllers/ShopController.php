<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShopModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index() {

        try {
            $data = ShopModel::get()->first();
            return $this->success($data,'get shop info',200);

        } catch(QueryException $e) {
            return $this->unknown();  
        }
    }

    public function create(Request $request) {
        $input = $request->only(['name', 'description', 'email', 'phone', 'address']);

        $validator = Validator::make($input, [
            "name" => 'required',
            "description" => 'required',
            'phone' => 'required',
            'address' => 'required'
        ]);

        if ($validator->fails()) {
      
            return $this-> unprocess($validator->errors()->first());
        }

        $shop = ShopModel::get()->first();

        if($shop) {
            return $this->unknown();
        }

        $newShop = new ShopModel;
        $newShop->name = $input['name'];
        $newShop->description = $input['description'];
        $newShop->phone = $input['phone'];
        $newShop->email = $input['email'];
        $newShop->address = $input['address'];

        try {
            $store = $newShop->save();
            return $this->suceess($newShop, 'shop is created');

        } catch(QueryException $e) {
            return $this->unknown();
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
            return $this->unprocess($validator->errors()->first());
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
               
                return $this->success($shop,'Shop is update');
    
            } catch(QueryException $e) {
                return $this->unkonwn();
            }
        }
        return $this->notFound('shop not found');
    }
}
