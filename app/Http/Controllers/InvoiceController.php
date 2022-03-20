<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\CreditModel;
use App\Models\InvoiceModel;
use App\Models\ItemModel;
use Illuminate\Http\Request;
use App\Exports\InvoiceExport;
use App\HttpResponse\ApiResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\QueryException;
use \Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }
// list of invoice does not include deleted invoice
    public function index()
    {
        try {
            $invoice = InvoiceModel::get()->first();
            
            if(!$invoice){
                $response = ApiResponse::Success([], 'No invoice data');
                return response()->json($response['json'], $response['status']);
            }

            $invoice = InvoiceModel::with(['credit'])->get();
            $response = ApiResponse::Success($invoice, 'get invoice list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    //  Get Last Invoice;
    public function lastInvoice () 
    {
        try {
            $invoice = InvoiceModel::latest()->first();
            $response = ApiResponse::Success($invoice, 'get invoice list');
            return response()->json($response['json'], $response['status']);
        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
    
    //Create Invoice and Credit
    public function create(Request $request)
    {
        $input = $request->only([
            'invoice_no', 
            'customer_name', 'customer_phone', 'customer_email', 'customer_address', 
            'invoice_data', 
            'total_amount',
            'discount', 
            'pay_amount',
            'credit_amount'
        ]);

        $validator = Validator::make($input, [
            "invoice_no" => 'required|unique:invoice',
            "invoice_data" => 'required',
            "total_amount" => 'required',
            "pay_amount" => 'required',
            'discount' => 'required',
            'credit_amount' => 'required'
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $updateItem = new ItemModel;

        foreach($input['invoice_data'] as $item) {
            $updateItem->where('code', $item['code'])->decrement('qty', $item['requestQty']);
        }

        $invoiceData = $input['invoice_data'];

        $input['invoice_data'] = json_encode($input['invoice_data']);

        try{
            $invoice = InvoiceModel::create($input);

            if((int)$input['credit_amount'] > 0) {
                $repayments = [];

                array_push($repayments, [
                    'pay_amount' => (float)$input['pay_amount'],
                    'pay_date' => Carbon::now()
                ]);

                $creditInput = [
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $input['invoice_no'],
                    'amount' => $input['credit_amount'],
                    'repayment' => json_encode($repayments)
                ];

                $credit = CreditModel::create($creditInput);
                $response = ApiResponse::Success([
                    "invoice" => $invoice,
                    "credit" => $credit
                ], 'invoice created');
                return response()->json($response['json'], $response['status']);
            }

            $response = ApiResponse::Success([
                "invoice" => $invoice,
                "credit" => null
            ], 'invoice created');
            return response()->json($response['json'], $response['status']);
            
        } catch (QueryException $e){
            dd($e);
            $response = ApiResponse::Unknown('unknown error');
            return response()->json($response['json'], $response['status']); 
        }
    }
//softDelete
    public function delete(Request $request)
    {
        $id = $request->id;

        try {
            $invoice = InvoiceModel::find($id);

            if(!$invoice){
                $response = ApiResponse::NotFound('invoice is not found');
                return response()->json($response['json'], $response['status']);
            }

            $invoice->delete();
            $response = ApiResponse::Success($invoice, 'invoice is deleted');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
//restore deleted invoice
    public function restore()
    {
        $invoice = InvoiceModel::onlyTrashed()->first();

        if(!$invoice){
            $response = ApiResponse::NotFound('No deleted invoice data');
            return response()->json($response['json'], $response['status']);
        }

        try {
            $invoice = InvoiceModel::onlyTrashed()->restore();
            $invoice = InvoiceModel::all();
            $response = ApiResponse::Success($invoice, 'Restore Success');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
//show deleted invoice list
    public function deletedList()
    {
        try {
            $invoice = InvoiceModel::onlyTrashed()->first();

            if(!$invoice){
                $response = ApiResponse::NotFound('No deleted invoice data');
                return response()->json($response['json'], $response['status']);
            }
            
            $invoice = InvoiceModel::onlyTrashed()->get();
            $response = ApiResponse::Success($invoice, 'deleted invoice list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
//Permanently Delete
    public function permanentDelete(Request $request)
    {
        try {
            $invoice = InvoiceModel::find($request->id);

            if(!$invoice) {
                $response = ApiResponse::NotFound('invoice is not found');
            } else {
                $invoice->forceDelete();
                $response = ApiResponse::Success($invoice, 'invoice is deleted');
            }

            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
//List by Date filter
    public function listByDate(Request $request)
    {
        $input = $request->only(['start_date','end_date']);
        $validator = Validator::make($input, [
            "start_date" => 'required',
            "end_date" => 'required',
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }
        try {
            $start=$request->start_date;
            $end=$request->end_date;
            $get = InvoiceModel::whereBetween('created_at', [$start, $end])->with(['credit'])->get()->all();
            $response = ApiResponse::Success($get, 'get invoice list');
            return response()->json($response['json'], $response['status']);   
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
//test for store fakeDate
    public function test(Request $request)
    {
        $input = $request->only(['created_at','invoice_id', 'customer_id', 'invoice_data', 'total_amount','discount','cash_back']);

            $invoice = InvoiceModel::create($input);
         
            $response = ApiResponse::Success($invoice, 'get invoice list');
            return response()->json($response['json'], $response['status']);
    }
//ExcelExport
    public function export() 
    {
       return Excel::download(new InvoiceExport, 'Invoices.xlsx');
    }
}
