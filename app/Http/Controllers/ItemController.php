<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\ItemModel;
use App\Imports\PriceImport;
use App\Models\SecondItem;
use App\Exports\ItemExport;
use App\Imports\ItemImport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CategoryModel;
use App\HttpResponse\ApiResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;

ini_set('max_execution_time', '900000');

class ItemController extends Controller
{

    public function __construct() {
        $this->middleware('jwt.verify', ['except' => ['importPrice']]);
        $this->middleware('license', ['except' => ['importPrice']]);
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
        $input = $request->only(['category_id', 'code', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'percentage', 'location', 'active']);

        $validator = Validator::make($input, [
            "eng_name" => "required",
            "code" => "required|unique:items"
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
        $input = $request->only(['category_id', 'code', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'percentage', 'fix_amount', 'location', 'active']);

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

    public function updatePercentage(Request $request) {
        $input = $request->only(['amount', 'type']);

        $validator = Validator::make($input, [
            "amount" => "required",
            "type" => "required"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $newItem = new ItemModel;
            if($input['type'] ==='increment') {
                $newItem->increment('percentage', $request->amount);
            }

            if($input['type'] === 'decrement') {
                $newItem->decrement('percentage', $request->amount);
            }

            $newItem->update();

            $response = ApiResponse::Success($newItem, "item's percentage are updated");
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

    public function importPrice() {
        $PricePath = storage_path('../database/seeders/xlsx/prices.xlsx');
        $rows = Excel::toArray(new PriceImport, $PricePath);

        $updateList = [];
        $createItemList = [];

        foreach($rows[0] as $row) {
            $item = [
                'code' => $row['material_code'],
                'price' => $row['price']
            ];

            $checkItem = ItemModel::where('code', $item['code'])->get()->first();

            if($checkItem) {
                $updateItem = ItemModel::where('code', $item['code'])->update([
                    'price' => $item['price'],
                ]);

                array_push($updateList, $item);
            } else {
                $createItem = [
                    'category_id' => 1, 
                    'code' => $item['code'], 
                    'eng_name' => null, 
                    'mm_name' => null, 
                    'model' => null, 
                    'qty' => 0, 
                    'price' => $item['price'], 
                    'percentage' => null, 
                    'location' => null, 
                    'active' => true
                ];

                ItemModel::create($createItem);
                array_push($createItemList, $item);
            }
        }

        $response = ApiResponse::Success([
            "update_price" => [
                "total" => count($updateList),
                "data" => $updateList
            ],
            "create_items" => [
                "total" => count($createItemList),
                "data" => $createItemList
            ]
        ], 'Success');
        return response()->json($response['json'], $response['status']);

    }

    public function export() 
    {
       return Excel::download(new ItemExport, 'Items.xlsx');
    }

    public function deleteMultiple(Request $request) {
        $ids=$request->data;
        $input = $request->only(['data']);
        
        $validator = Validator::make($input, [
            "data" => "required"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $deleteditem = ItemModel::find($ids);
            $ids = $input['data'];
            $item = ItemModel::whereIn('id', $ids)->delete();
        
            $response = ApiResponse::Success($deleteditem, 'items are deleted');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
