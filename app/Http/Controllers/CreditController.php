<?php

namespace App\Http\Controllers;

use App\Models\CreditModel;
use Illuminate\Http\Request;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Validator;
use \Carbon\Carbon;

class CreditController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    //get Credit List
    public function index()
    {
        try {
            $credits = CreditModel::with(['invoice'])->get();
            $response = ApiResponse::Success($credits, 'get credits list');
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
            $credit = CreditModel::where('invoice_id',$request->id)->with(['invoice'])->get()->first();

            if(!$credit){
                $response = ApiResponse::NotFound('Credit not found');
                return response()->json($response['json'], $response['status']);
            }

            $credit->repayment = json_decode($credit->repayment);
            $credit->invoice->invoice_data = json_decode($credit->invoice->invoice_data);

            $response = ApiResponse::Success($credit, 'get credit list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    //update Credit by invoice_id
    public function update(Request $request)
    {
        $input = $request->only(['pay_amount']);

        $validator = Validator::make($input, [
            "pay_amount" => 'required'
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $input['pay_date'] = Carbon::now();
        

        try{
            $credit = CreditModel::find($request->id);

            if(!$credit){
                $response = ApiResponse::NotFound('Credit not found');
                return response()->json($response['json'], $response['status']);
            }

            $data = json_decode($credit->repayment);
            array_push($data, $input);


            $credit = CreditModel::where('id',$request['id'])->update(['repayment'=> json_encode($data)]);
            
            $response = ApiResponse::Success($credit, 'get credit list');
            return response()->json($response['json'], $response['status']);

        }catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
