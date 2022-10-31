<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\CategoryModel;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class CategoryController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index (Request $request) {
        $pageSize = $request->pageSize ? $request->pageSize : 50;
        try {
            $categories = CategoryModel::orderBy('created_at', 'desc')->paginate($pageSize);
            return $this->success($categories , 'Categories List' , 200);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
    public function withItem (Request $request) {
        if((boolean)$request['status'] === true) {
            try {
                $categories = CategoryModel::with(['item'])->get();
                $response = ApiResponse::Success($categories, 'get categories list with items');
                return response()->json($response['json'], $response['status']);
            } catch (QueryException $e) {
                $response = ApiResponse::Unknown('someting was wrong');
                return response()->json($response['json'], $response['status']);
            }
        }  else {
            $response = ApiResponse::BedRequest('invalid item status');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function create (Request $request) {
        $input = $request->only(['name', 'description']);

        $validator = Validator::make($input, [
            "name" => 'required|unique:category'
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
        }

        try {
            $newCategory = CategoryModel::create($input);
            $response = ApiResponse::Success($newCategory, 'get categories list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update (Request $request) {
        $id = $request->id;
        $input = $request->only(['name', 'description']);

        $validator = Validator::make($input, [
            "name" => 'unique:category'
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }


        try {
            $category = CategoryModel::find($id);

            if(!$category) {
                $response = ApiResponse::NotFound('category is not found');
                return response()->json($response['json'], $response['status']);
            }

            $affectedRows = CategoryModel::where('id', '=', $id)->update($input);
            $category->refresh();
            $response = ApiResponse::Success($category, 'get categories list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function delete (Request $request) {
        $id = $request->id;

        try {
            $category = CategoryModel::find($id);

            if(!$category) {
                $response = ApiResponse::NotFound('category is not found');
                return response()->json($response['json'], $response['status']);
            }

            $category->delete();
            $response = ApiResponse::Success($category, 'category is deleted');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function deleteMultiple(Request $request) {
        $ids=$request->data;
        $input = $request->only(['data']);
        $deleteditem = CategoryModel::find($ids);
        $validator = Validator::make($input, [
            "data" => "required"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $ids = $input['data'];
            $item = CategoryModel::whereIn('id', $ids)->delete();
            $response = ApiResponse::Success($deleteditem, 'items are deleted');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }

    }

    public function category (Request $request) {
        $id = $request->id;

        try {
            $category = CategoryModel::with(['items'])->find($id);

            if(!$category) {
                $response = ApiResponse::NotFound('category is not found');
                return response()->json($response['json'], $response['status']);
            }

            $response = ApiResponse::Success($category, 'category is retrived');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
