<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryModel;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index(Request $request)
    {
        $pageSize = $request->pageSize ? $request->pageSize : 50;
        try {
            $categories = CategoryModel::orderBy('created_at', 'desc')->paginate($pageSize);
            return $this->success($categories, 'Categories List');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
    public function withItem(Request $request)
    {
        if ((bool)$request['status'] === true) {
            try {
                $categories = CategoryModel::with(['item'])->get();
                return $this->success($categories, 'get categories list with items');
            } catch (QueryException $e) {
                return $this->unknown();
            }
        } else {
            return $this->unprocess('invalid item status');
        }
    }

    public function create(Request $request)
    {
        $input = $request->only(['name', 'description']);
        $validator = Validator::make($input, [
            "name" => 'required|unique:category'
        ]);
        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }
        try {
            $newCategory = CategoryModel::create($input);
            return $this->success($newCategory, 'get categories list', 201);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
    public function update(Request $request)
    {
        $id = $request->id;
        $input = $request->only(['name', 'description']);
        $validator = Validator::make($input, [
            "name" => 'unique:category'
        ]);
        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }
        try {
            $category = CategoryModel::find($id);
            if (!$category) {
                return $this->notFound('category is not found');
            }
            $affectedRows = CategoryModel::where('id', '=', $id)->update($input);
            $category->refresh();
            return $this->success($category, 'get categories list', 201);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
    public function delete(Request $request)
    {
        $id = $request->id;
        try {
            $category = CategoryModel::find($id);
            if (!$category) {
                return $this->notFound('category is not found');
            }
            $category->delete();
            return $this->success($category, 'category is deleted');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
    public function deleteMultiple(Request $request)
    {
        $ids = $request->data;
        $input = $request->only(['data']);
        $deleteditem = CategoryModel::find($ids);
        $validator = Validator::make($input, [
            "data" => "required"
        ]);
        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }
        try {
            $ids = $input['data'];
            $item = CategoryModel::whereIn('id', $ids)->delete();
            return $this->success($deleteditem, 'items are deleted');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
    public function category(Request $request)
    {
        $id = $request->id;
        try {
            $category = CategoryModel::with(['items'])->find($id);
            if (!$category) {
                return $this->notFound('category is not found');
            }
            return $this->success($category, 'category is retrived');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
}