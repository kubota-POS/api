<?php

namespace App\Http\Controllers;

use Validator;
use Carbon\Carbon;
use App\Models\CreditModel;
use App\Models\InvoiceModel;
use Illuminate\Http\Request;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class DashboardController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function customerInfo()
    {
        try {
            $unknown=InvoiceModel::where('customer_name','=',null)->count();
            $customerInfo=InvoiceModel::distinct('customer_phone')->count();
            $customerNoPh=InvoiceModel::where('customer_phone','=',null)->where('customer_name','!=',null)->distinct('customer_name')->count();
            $credit=CreditModel::all();
            $data=[];
            $data['credit_amount']=0;
            $data['repayment_amount']=0;
            
            foreach($credit as $e){
                $data['credit_amount']+=$e['amount'];
                if($e['repayment']){
                $re=json_decode($e['repayment']);
                $data['repayment_amount']+=$re->pay_amount;}
            }
            $data['remain_amount']=$data['credit_amount']-$data['repayment_amount'];
            $knownCustomer=$customerInfo+$customerNoPh;
            $totalCustomer=$unknown+$knownCustomer;
        
            $info=[];
            $info['Unknow Customer']=$unknown;
            $info['known Customer']=$knownCustomer;
            $info['Total Customer']=$totalCustomer;
            $info['credit']=$data;
            
            $response = ApiResponse::Success($info, 'Customer Info with Credit');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        } 
    }

    public function invoiceInfo()
    {
        try {
        $data=[];
        $today_date = Carbon::now();
        $last_week = Carbon::now()->subDays(7);
        $last_month = Carbon::now()->subDays(31);
        
        // Today_Invoice_info
        $today_invoice = InvoiceModel::whereDate('created_at', '=', $today_date)->with(['credit'])->get();
        
        $today=[];
        $today['total_amount']=0;
        $today['total_credit']=0;
        $today['total_payamount']=0;
        $today['remaning_balance']=0;
        foreach($today_invoice as $e){
            $today['total_amount']+=$e['total_amount'];
            $today['total_credit']+=$e['credit']['amount'];
            $today['total_payamount']+=$e['pay_amount'];
            $today['remaning_balance']=$today['total_credit'];
        }
        $today['total_invoice']=$today_invoice->count();
        $data['today']=$today;
        

        // Weekly_Invoice_info
        $weekly_invoice = InvoiceModel::whereBetween('created_at', [$last_week, $today_date])->with(['credit'])->get();
        $week=[];
        $week['total_amount']=0;
        $week['total_credit']=0;
        $week['total_payamount']=0;
        $week['remaning_balance']=0;
        foreach($weekly_invoice as $e){
            $week['total_amount']+=$e['total_amount'];
            $week['total_credit']+=$e['credit']['amount'];
            $week['total_payamount']+=$e['pay_amount'];
            $week['remaning_balance']=$week['total_credit'];
        }
        $week['total_invoice']=$weekly_invoice->count();
        $data['week']=$week;



        // Monthly_Invoice_info
        $monthly_invoice = InvoiceModel::whereBetween('created_at', [$last_month, $today_date])->with(['credit'])->get();
        $month=[];
        $month['total_amount']=0;
        $month['total_credit']=0;
        $month['total_payamount']=0;
        $month['remaning_balance']=0;
        foreach($monthly_invoice as $e){
            $month['total_amount']+=$e['total_amount'];
            $month['total_credit']+=$e['credit']['amount'];
            $month['total_payamount']+=$e['pay_amount'];
            $month['remaning_balance']=$month['total_credit'];
        }
        $month['total_invoice']=$monthly_invoice->count();
        $data['month']=$month;

        $response = ApiResponse::Success($data, 'Invoice Info');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        } 
    }
}
