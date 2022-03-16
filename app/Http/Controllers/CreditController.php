<?php

namespace App\Http\Controllers;

use App\Models\CreditModel;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify', 'device']);
    }
    
    public function index()
    {
        return CreditModel::with(['invoice'])->get();
    }

    public function update(Request $request)
    {
        return "OK";
    }
}
