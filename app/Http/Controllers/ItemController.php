<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\ItemModel;
use App\Models\CategoryModel;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class ItemController extends Controller
{

    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index () {
        try {
            $items = ItemModel::with(['category'])->get();
            $response = ApiResponse::Success($items, 'get all items');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function create (Request $request) {
        $input = $request->only(['category_id', 'code', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'location', 'active']);

        $validator = Validator::make($input, [
            "eng_name" => "required",
            "code" => "required|unique:items",
            "model" => "required|unique:items"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $newItem = ItemModel::create($input);
            $response = ApiResponse::Success($newItem, 'new item is created');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function detail (Request $request) {
        $id = $request->id;

        try {
            $item = ItemModel::with(['category'])->find($id);

            if($item) {
                $response = ApiResponse::Success($item, 'get item detail');
            } else {
                $response = ApiResponse::NotFound('item does not found');
            }

            return response()->json($response['json'], $response['status']);  

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);   
        }
    }

    public function update (Request $request) {
        $id = $request->id;
        $input = $request->only(['category_id', 'code', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'location', 'active']);

        try {
            $item = ItemModel::with(['category'])->find($id);

            if(!$item) {
                $response = ApiResponse::NotFound('item is not found');
            } else {
                $item->update($input);
                $response = ApiResponse::Success($item, 'item is updated');
            }

            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function delete(Request $request) {
        $id = $request->id;

        try {

            $item = ItemModel::find($id);

            if(!$item) {
                $response = ApiResponse::NotFound('item is not found');
            } else {
                $item->delete();
                $response = ApiResponse::Success($item, 'item is deleted');
            }

            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
