<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index()
    {
        try {

            $customer = CustomerModel::with(['invoice', 'credit'])->get();
            return $this->success($customer, 'get customer list');
        } catch (QueryException $e) {
            return $this->unknown();
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
            return $this->unprocess($validator->errors()->first());
        }

        try{
            $customer = CustomerModel::create($input);
            return $this->success($customer , 'get customer list' ,201);
        } catch (QueryException $e){
            return $this->unknow();
        }
    }

    public function detail(Request $request)
    {
        $id = $request->id;

        try {
            $customer = CustomerModel::find($id);

            if(!$customer){
                return $this->notFound('customer is not found');
            }

            return $this->success($customer, 'customer is retrived');
        } catch (QueryException $e) {
            return $this->unknown();
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
            return $this->unprocess($validator->errors()->first());
        }

        try {
            $customer = CustomerModel::find($id);

            if(!$customer){
                return $this->notFound('customer is not found');
            }

            $update = CustomerModel::where('id', '=', $id)->update($input);
            $customer->refresh();
            return $this->success($customer, 'get updated customer');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function delete(Request $request)
    {
        $id = $request->id;

        try {
            $customer = CustomerModel::find($id);

            if(!$customer){
                return $this->notFound('customer is not found');
            }

            $customer->delete();
            return $this->success($customer, 'customer is deleted');
        } catch (QueryException $e) {
            return $this->unknown();
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
            return $this->unprocess($validator->errors()->first());
        }

        try {
            $ids = $input['data'];
            $customer = CustomerModel::whereIn('id',$ids)->delete();
            return $this->success($deletedCustomer, 'Customers are deleted');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }
}
