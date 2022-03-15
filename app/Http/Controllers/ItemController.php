<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\ItemModel;
use App\Models\SecondItem;
use App\Exports\ItemExport;
use App\Imports\ItemImport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\CategoryModel;
use App\HttpResponse\ApiResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
ini_set('max_execution_time', '900');

class ItemController extends Controller
{

    public function __construct() {
        $this->middleware(['license', 'jwt.verify', 'device']);
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
        $input = $request->only(['category_id', 'code', 'eng_name', 'mm_name', 'model', 'qty', 'price', 'location', 'active','percentage','fix_amount']);

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

    public function import() 
    {
        $path = storage_path('app/list.xlsx');
        Excel::import(new ItemImport, $path);
        
        $item = ItemModel::all();
        return "Sucessfully Imported";
    }

    public function export() 
    {
       return Excel::download(new ItemExport, 'Items.xlsx');
    }

    public function deleteMultiple(Request $request) {
        $ids=$request->data;
        $input = $request->only(['data']);
        $deleteditem = ItemModel::find($ids);
        $validator = Validator::make($input, [
            "data" => "required"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $ids = $input['data'];
            $item = ItemModel::whereIn('id', $ids)->delete();
            $response = ApiResponse::Success($deleteditem, 'items are deleted');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }

    }

    public function changePercent(Request $request)
    {
        
        $input = $request->only(['data']);
        $validator = Validator::make($input, [
            "data" => "required"
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }
        $data = $request->data;
        $plus = Str::contains($data, '+');
        $minus = Str::contains($data, '-');
        $num = (int)substr($data,1);


        $model = ItemModel::get()->all();
        $end = count($model);
        try {
            if($plus){
                for ($i=0; $i < $end; $i++) { 
                    $model[$i]['percentage']+=$num;
                    $new=$model[$i];
                    $new->save();
                }
            }
            
            if($minus){
                for ($i=0; $i < $end; $i++) { 
                    $model[$i]['percentage']-=$num;
                    $new=$model[$i];
                    $new->save();
                }
            }    

            $item = ItemModel::get()->all();
            $response = ApiResponse::Success($item, 'All Percentage are updated');
            return response()->json($response['json'], $response['status']);

        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
        
    }
    
//Import Item data from another existing Db item table except 'price code'
    public function importAnotherDb()
    {
        
        $second = SecondItem::get()->all();
        $item = ItemModel::get()->all();
       
        $end = count($second);
        $fend = count($item);
        
        for ($i=0; $i < $end; $i++) {        
            for($j=0; $j < $fend; $j++ ){
                if($second[$i]['m_code']==$item[$j]['code']){
                    $item[$j]['qty'] = $second[$i]['m_qty'];
                    $item[$j]['percentage'] = $second[$i]['sell_percentage'];
                    $item[$j]['location'] = $second[$i]['location'];
                    $item[$j]['price'] = $second[$i]['price_code'];
                    $new=$item[$j];
                    $new->save();   
                }
            }
        }
        return "Merge DB Complete";       
    }

//After importing delte no data row(Optional)
    public function deleteNoData()
    {
        $item = ItemModel::where('qty','=','0')->delete();
        return "Delete Complete";
    }

//Change Price Code to Price value from Imported Item table
    public function codeToPrice()
    {
        $item = ItemModel::get();
        $end = count($item);
        for ($i=0; $i < $end; $i++){
            
            $code = $item[$i]['price'];
            $arr = str_split($code);
           
            for ($j=0; $j < count($arr); $j++){
                if ($arr[$j]=='A') {
                    $arr[$j]='1';
                }
                if ($arr[$j]=='X') {
                    $arr[$j]='2';
                }
                if ($arr[$j]=='E') {
                    $arr[$j]='3';
                }
                if ($arr[$j]=='P') {
                    $arr[$j]='4';
                }
                if ($arr[$j]=='R') {
                    $arr[$j]='5';
                }
                if ($arr[$j]=='O') {
                    $arr[$j]='6';
                }
                if ($arr[$j]=='D') {
                    $arr[$j]='7';
                }
                if ($arr[$j]=='U') {
                    $arr[$j]='8';
                }
                if ($arr[$j]=='C') {
                    $arr[$j]='9';
                }
                if ($arr[$j]=='T') {
                    $arr[$j]='0';
                }
            }
            $price = implode($arr);
            $item[$i]['price']=$price;
            $item[$i]->save();
        }

        return "Change Complete";
    }
}
