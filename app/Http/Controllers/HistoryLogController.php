<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\HistoryLogModel;
use Illuminate\Support\Facades\Auth;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;

class HistoryLogController extends Controller
{
    public function __construct() {
        $this->middleware(['license', 'jwt.verify']);
    }

    public function index (Request $request) {
        $type = $request->type;
        $page = $request->page;
        $limit = 10;

        if((int) $page === 1) {
            $start = 0;
        } else {
            $start = $limit * $page;
        }
        
        try {
            $history_logs = HistoryLogModel::where('type', $request->type)->offset($start)->limit($limit)->orderBy('created_at', 'DESC')->get();

            $data = [
                'type' => $type,
                'history' => $history_logs
            ];

            $response = ApiResponse::Success($data, 'get history log');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function create (Request $request) {
        $input = $request->only(['type', 'action', 'description']);

        $validator = Validator::make($input, [
            "type" => 'required',
            "action" => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        try {
            $newHistoryLog = HistoryLogModel::create($input);
            $response = ApiResponse::Success($newHistoryLog, 'historylog is created');
            return response()->json($response['json'], $response['status']);
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('someting was wrong');
            return response()->json($response['json'], $response['status']);
        }
    }
}
