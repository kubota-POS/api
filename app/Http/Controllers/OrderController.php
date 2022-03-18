<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Validator;

class OrderController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify', 'device']);
    }

    public function index()
    {
        try {
            $order = Order::get();
            $response = ApiResponse::Success($order, 'get order list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()-json($response['json'], $response['status']);
        }
    }

    public function create(Request $request)
    {
        $input = $request->only(['item_id', 'merchant_id', 'qty', 'total_amount', 'credit', 'pay_type', 'pay_amount']);

        $validator = Validator::make($input, [
            "item_id" => 'required',
            "merchant_id" => 'required',
            "qty" => 'required',
            "total_amount" => 'required',
            "pay_type" => 'required',
            "pay_amount" => 'required',
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try{
            $order = Order::create($input);
            $response = ApiResponse::Success($order, 'get order list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e){
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'],$response['status']);
        }
    }

    public function detail(Request $request)
    {
        $id = $request->id;

        try {
            $order = Order::find($id);

            if(!$order){
                $response = ApiResponse::NotFound('Order is not found');
                return response()->json($response['json'], $response['status']);
            }

            $response = ApiResponse::Success($order, 'Order is retrived');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $order = Order::find($id);

            if(!$order){
                $response = ApiResponse::NotFound('Order is not found');
                return response()->json($response['json'], $response['status']);
            }
        $input = $request->only(['item_id', 'merchant_id', 'qty', 'total_amount', 'credit', 'pay_type', 'pay_amount']);

        $validator = Validator::make($input, [
            "item_id" => 'required',
            "merchant_id" => 'required',
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $update = Order::where('id', '=', $id)->update($input);
            $order->refresh();
            $response = ApiResponse::Success($order, 'get updated order');
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
            $order = Order::find($id);

            if(!$order){
                $response = ApiResponse::NotFound('order is not found');
                return response()->json($response['json'], $response['status']);
            }

            $order->delete();
            $response = ApiResponse::Success($order, 'Order is deleted');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
