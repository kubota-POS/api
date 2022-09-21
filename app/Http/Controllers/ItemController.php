<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\Validator;

ini_set('max_execution_time', '900000');

class ItemController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['importPrice']]);
        $this->middleware('license', ['except' => ['importPrice']]);
    }

    public function index()
    {
        try {
            $items = ItemModel::with(['category'])->get();
            return $this->success($items, 'get all items');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function create(Request $request)
    {
        $input = $request->only(['category_id', 'code', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'percentage', 'location', 'active']);

        $validator = Validator::make($input, [
            "eng_name" => "required",
            "code" => "required|unique:items"
        ]);

        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }

        try {
            $newItem = ItemModel::create($input);
            return $this->success($newItem, 'new item is created', 201);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function detail(Request $request)
    {
        $id = $request->id;

        try {
            $item = ItemModel::with(['category'])->find($id);

            if ($item) {
                return $this->success($item, 'get item detail');
            } else {
                return $this->notFound('item does not found');
            }
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function update(Request $request)
    {
        $id = $request->id;
        $input = $request->only(['category_id', 'code', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'percentage', 'fix_amount', 'location', 'active']);

        try {
            $item = ItemModel::with(['category'])->find($id);

            if (!$item) {
                return $this->notFound('item is not found');
            } else {
                $item->update($input);
                return $this->success($item, 'item is updated', 201);
            }
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function updatePercentage(Request $request)
    {
        $input = $request->only(['amount', 'type']);

        $validator = Validator::make($input, [
            "amount" => "required",
            "type" => "required"
        ]);

        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }

        try {
            $newItem = new ItemModel;
            if ($input['type'] === 'increment') {
                $newItem->increment('percentage', $request->amount);
            }

            if ($input['type'] === 'decrement') {
                $newItem->decrement('percentage', $request->amount);
            }

            $newItem->update();

            return $this->success($newItem, "item's percentage are updated", 201);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        try {
            $item = ItemModel::find($id);

            if (!$item) {
                return $this->notFound('item is not found');
            } else {
                $item->delete();
                return $this->success($item, 'item is deleted');
            }
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function importPrice()
    {
        $PricePath = storage_path('../database/seeders/xlsx/prices.xlsx');
        $rows = Excel::toArray(new PriceImport, $PricePath);

        $updateList = [];
        $createItemList = [];

        foreach ($rows[0] as $row) {
            $item = [
                'code' => $row['material_code'],
                'price' => $row['price']
            ];

            $checkItem = ItemModel::where('code', $item['code'])->get()->first();

            if ($checkItem) {
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

    public function deleteMultiple(Request $request)
    {
        $ids = $request->data;
        $input = $request->only(['data']);

        $validator = Validator::make($input, [
            "data" => "required"
        ]);

        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }

        try {
            $deleteditem = ItemModel::find($ids);
            $ids = $input['data'];
            $item = ItemModel::whereIn('id', $ids)->delete();

            return $this->success($deleteditem, 'items are deleted');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
}
