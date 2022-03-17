<?php

namespace App\Http\Controllers;

use App\Models\SecondItem;
use Illuminate\Http\Request;
use App\Imports\SecondItemImport;
use Maatwebsite\Excel\Facades\Excel;

class SecondItemController extends Controller
{
    public function index()
    {
        $item = SecondItem::get();
        return $item;
    }

    public function import() 
    {
        $path = storage_path('app/item.xlsx');
        Excel::import(new SecondItemImport, $path);
        
        $item = SecondItem::all();
        return "Sucessfully Imported";
    }
}
