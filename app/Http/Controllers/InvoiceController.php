<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\CreditModel;
use App\Models\CustomerModel;
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
            $invoice = InvoiceModel::with(['customer','credit'])->get();

            if(!$invoice){
                return $this->Success([], 'No invoice data');
            }
            
            return $this->Success($invoice, 'get invoice list');
        } catch (QueryException $e) {
            return $this->Unknown('something was wrong');
        }
    }

    //  Get Last Invoice;
    public function lastInvoice () 
    {
        try {
            $invoice = InvoiceModel::latest()->first();
            return $this->Success($invoice, 'get invoice list');
        } catch(QueryException $e) {
            return $this->Unknown('something was wrong');
        }
    }
    
    //Create Invoice and Credit
    public function create(Request $request)
    {
        $input = $request->only([
            'invoice_no', 
            'customer_id',
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
            return $this->BedRequest($validator->errors()->first());
        }

        $updateItem = new ItemModel;

        foreach($input['invoice_data'] as $item) {
            $updateItem->where('id', $item['id'])->decrement('qty', $item['requestQty']);
        }

        $invoiceData = $input['invoice_data'];

        $input['invoice_data'] = json_encode($input['invoice_data']);

        if(isset($input['customer_id'])) {
            $customer = CustomerModel::find($input['customer_id']) || null;
        } else {
            $customer = null;
        }

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
                    "credit" => $credit,
                    "customer" => $customer
                ], 'invoice created');

                return response()->json($response['json'], $response['status']);
            }

            $response = ApiResponse::Success([
                "invoice" => $invoice,
                "credit" => null,
                "customer" => $customer
            ], 'invoice created');

            return response()->json($response['json'], $response['status']);
            
        } catch (QueryException $e){
            return $this->Unknown('unknown error');
        }
    }
    
    //softDelete
    public function delete(Request $request)
    {
        $id = $request->id;

        try {
            $invoice = InvoiceModel::find($id);

            if(!$invoice){
                return $this->NotFound('invoice is not found');
            }

            $invoice->delete();
            return $this->Success($invoice, 'invoice is deleted');
        } catch (QueryException $e) {
            return $this->Unknown('something was wrong');
        }
    }
//restore deleted invoice
    public function restore()
    {
        $invoice = InvoiceModel::onlyTrashed()->first();

        if(!$invoice){
            return $this->NotFound('No deleted invoice data');
        }

        try {
            $invoice = InvoiceModel::onlyTrashed()->restore();
            $invoice = InvoiceModel::all();
            return $this->Success($invoice, 'Restore Success');
        } catch (QueryException $e) {
            return $this->Unknown('something was wrong');
        }
    }
//show deleted invoice list
    public function deletedList()
    {
        try {
            $invoice = InvoiceModel::onlyTrashed()->first();

            if(!$invoice){
                return $this->NotFound('No deleted invoice data');
            }
            
            $invoice = InvoiceModel::onlyTrashed()->get();
            return $this->Success($invoice, 'deleted invoice list');
        } catch (QueryException $e) {
            return $this->Unknown('something was wrong');
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
            return $ths->Unknown('someting was wrong');
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
            return $this->BedRequest($validator->errors()->first());
        }
        try {
            $start=$request->start_date;
            $end=$request->end_date;
            $get = InvoiceModel::whereBetween('created_at', [$start, $end])->with(['credit'])->get()->all();
            return $this->Success($get, 'get invoice list');
        } catch (QueryException $e) {
            return $this->Unknown('someting was wrong');
        }
    }
//test for store fakeDate
    public function test(Request $request )
    {
        $input = $request->only(['created_at','invoice_id', 'customer_id', 'invoice_data', 'total_amount','discount','cash_back']);

            $invoice = InvoiceModel::create($input);
            return $this->Success($invoice, 'get invoice list');
    }
//ExcelExport
    public function export() 
    {
       return Excel::download(new InvoiceExport, 'Invoices.xlsx');
    }
}
