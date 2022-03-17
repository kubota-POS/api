<?php

namespace App\Http\Controllers;

use App\Models\CreditModel;
use Illuminate\Http\Request;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Validator;

class CreditController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify', 'device']);
    }
//get Credit List
    public function index()
    {
        try {
            $credit = CreditModel::get()->first();
            
            if(!$credit){
                $response = ApiResponse::NotFound('No credit data');
                return response()->json($response['json'], $response['status']);
            }
            $credit = CreditModel::with(['invoice'])->get();
            $response = ApiResponse::Success($credit, 'get credit list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
//get Credit detail by Invoice_ID
    public function show(Request $request)
    {
        try {
            $credit = CreditModel::where('invoice_no',$request['id'])->get()->first();
            if(!$credit){
                $response = ApiResponse::NotFound('Credit not found');
                return response()->json($response['json'], $response['status']);
            }
            $credit = CreditModel::where('invoice_no',$request['id'])->with(['invoice'])->get();
            $response = ApiResponse::Success($credit, 'get credit list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
//update Credit by invoice_id
    public function update(Request $request)
    {
        $input = $request->only(['repayment']);

        $validator = Validator::make($input, [
            "repayment" => 'required'
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }
        try{
        $data = json_encode($request->repayment);
        $credit = CreditModel::where('invoice_no',$request['id'])->get()->first();
        if(!$credit){
            $response = ApiResponse::NotFound('Credit not found');
            return response()->json($response['json'], $response['status']);
        }
        $credit->repayment = $data;
        $amount = $request->repayment['pay_amount'];
        $credit->amount = $credit->amount - $amount;
        $credit->credit_date = $request->repayment['pay_date'];
        $credit->save();
       
        $credit = CreditModel::where('invoice_no',$request['id'])->with(['invoice'])->get();
        $response = ApiResponse::Success($credit, 'get credit list');
        return response()->json($response['json'], $response['status']);
        }catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
