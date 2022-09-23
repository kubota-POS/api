<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\NumberSpecificationModel;
use App\Models\HistoryLogModel;
use Illuminate\Support\Facades\Auth;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class NumberSpecificationController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index () {
        try {
            $numberSpecification = NumberSpecificationModel::get();
            return $this->Success($numberSpecification, 'get number specification list');
        } catch (QueryException $e) {
            return $thit->Unknown('someting was wrong');
        }
    }

    public function check() {
        try {
            $activeNumber = NumberSpecificationModel::where('active', true)->get();

            if(count($activeNumber) === 0) {
                $response = ApiResponse::Success([], 'no record found');
            } else {
                $response = ApiResponse::Success($activeNumber, 'active number specification list');   
            }

            return response()->json($response['json'], $response['status']);
        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update(Request $request) {
        $id = $request->id;
        $input = $request->only(['set_char']);
        $user = Auth::user();

        $validator = Validator::make($input, [
            "set_char" => 'required|unique:number_specification|max:1|min:1|alpha'
        ]);

        if ($validator->fails()) {
            return $this->BedRequest($validator->errors()->first());
        }

        $input['set_char'] = strtoupper($input['set_char']);
        $updateChar = $input['set_char'];
        $setNumber = NumberSpecificationModel::find($id);
        $description = "$user->name is updated set number $setNumber->set_number value's $setNumber->set_char to {$input['set_char']}.";

        if($setNumber) {
            $setNumber->set_char = $input['set_char'];
    
            $history = [
                "user_id" => $user->id,
                "type" => 'number',
                "action" => "UPDATE",
                "description" => $description
            ];
     
            try {
                $update = $setNumber->push();
                $saveRecord = HistoryLogModel::create($history);
                return $this->Success($setNumber, 'number specification is updated');
    
            } catch(QueryException $e) {
                return $this->Unknown('something was wrong');
            }
        }
        return $this->NotFound('number specification not found');
    }
}
