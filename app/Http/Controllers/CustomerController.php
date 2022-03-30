<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\CustomerModel;
use App\Models\InvoiceModel;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class CustomerController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index()
    {
        try {

            $customer = CustomerModel::get();
            $response = ApiResponse::Success($customer, 'get customer list');

            // $customers = InvoiceModel::with(['credit'])->get();

            // $customerList = [];
            // $unknownCustomerList = [];

            // foreach($customers as $customer) {
            //     if($customer['customer_name'] !== null) {
            //         array_push($customerList, $customer);
            //     } else {
            //         array_push($unknownCustomerList, $customer);
            //     }
            // }

            // $responseData = [
            //     "customers" => $customerList,
            //     "total_customer" => count($customerList),
            //     "unknown_customers" => $unknownCustomerList,
            //     "total_unknown_customers" => count($unknownCustomerList)
            // ];

            // $response = ApiResponse::Success($responseData, 'get customer list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()-json($response['json'], $response['status']);
        }
    }
    
    public function create(Request $request)
    {
        $input = $request->only(['name', 'email', 'phone', 'address']);

        $validator = Validator::make($input, [
            "name" => 'required',
            "phone" => 'required'
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try{
            $customer = CustomerModel::create($input);
            $response = ApiResponse::Success($customer, 'get customer list');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e){
            $response = ApiResponse::Unknow('something was wrong');
            return response()->json($response['json'],$response['status']);
        }
    }

    public function detail(Request $request)
    {
        $id = $request->id;

        try {
            $customer = CustomerModel::find($id);

            if(!$customer){
                $response = ApiResponse::NotFound('customer is not found');
                return response()->json($response['json'], $response['status']);
            }

            $response = ApiResponse::Success($customer, 'customer is retrived');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
    
    public function update(Request $request)
    {
        $id = $request->id;
        $input = $request->only(['name', 'phone', 'email', ' address']);

        $validator = Validator::make($input, [
            "phone" => 'unique:customers'
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $customer = CustomerModel::find($id);

            if(!$customer){
                $response = ApiResponse::NotFound('customer is not found');
                return response()->json($response['json'], $response['status']);
            }

            $update = CustomerModel::where('id', '=', $id)->update($input);
            $customer->refresh();
            $response = ApiResponse::Success($customer, 'get updated customer');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        try {
            $customer = CustomerModel::find($id);

            if(!$customer){
                $response = ApiResponse::NotFound('customer is not found');
                return response()->json($response['json'], $response['status']);
            }

            $customer->delete();
            $response = ApiResponse::Success($customer, 'customer is deleted');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->data;
        $input = $request->only(['data']);
        $deletedCustomer = CustomerModel::find($ids);
        $validator = Validator::make($input, [
            "data" => "required"
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $ids = $input['data'];
            $customer = CustomerModel::whereIn('id',$ids)->delete();
            $response = ApiResponse::Success($deletedCustomer, 'Customers are deleted');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('something was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
