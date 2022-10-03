<?php

namespace App\Http\Controllers;

use App\Models\CreditModel;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
            return $this->success($credits, 'get credits list');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    //get Credit detail by Invoice_ID
    public function show(Request $request)
    {
        try {
            $credit = CreditModel::where('invoice_id',$request->id)->with(['invoice'])->get()->first();

            if(!$credit){
                return $this->notFound('Credit not found');
            }

            $credit->repayment = json_decode($credit->repayment);
            $credit->invoice->invoice_data = json_decode($credit->invoice->invoice_data);
            return $this->success($credit, 'get credit list');
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
            return $this->unprocess($validator->errors()->first());
            
        }

        $input['pay_date'] = Carbon::now();
        

        try{
            $credit = CreditModel::find($request->id);

            if(!$credit){
                return $this->notFound('Credit not found');
            }

            $data = json_decode($credit->repayment);
            array_push($data, $input);


            $credit = CreditModel::where('id',$request['id'])->update(['repayment'=> json_encode($data)]);
            return $this->success($credit, 'get credit list');

        }catch (QueryException $e) {
            return $this->unknown();
        }
    }
}
