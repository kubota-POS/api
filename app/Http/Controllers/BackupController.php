<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\BackupModel;
use App\Models\ItemModel;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class BackupController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index () {

        $items = BackupModel::get();

        $charSets = array("T", "A", "X", "E", "P", "R", "O", "D", "U", "C");
        $numberSets = array(0,1,2,3,4,5,6,7,8,9);

        $itemPush = [];

        foreach($items as $item) {
            $updateItem = [];
            $updateItem['category_id'] = 1;
            $updateItem['code'] = $item['m_code'];
            $updateItem['eng_name'] = $item['m_name'];
            $updateItem['mm_name'] = $item['m_name'];
            $updateItem['model'] = null;
            $updateItem['price'] = str_replace($charSets,$numberSets, $item['price_code']);
            $updateItem['qty'] = $item['m_qty'];
            $updateItem['percentage'] = $item['sell_percentage'];
            $updateItem['location'] = $item['location'];

            $newItem = ItemModel::create($updateItem);
            array_push($itemPush, $newItem);
        }
        return $this->success($itemPush, 'get categories list');
    }
}
