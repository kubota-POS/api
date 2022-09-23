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
            return $this->Success($data, 'get history log');
        } catch (QueryException $e) {
            return $this->Unknown('someting was wrong');
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
            return $this->BedRequest($validator->errors()->first());
        }

        try {
            $newHistoryLog = HistoryLogModel::create($input);
            return $this->Success($newHistoryLog, 'historylog is created');
        } catch (QueryException $e) {
            return $this->Unknown('someting was wrong');
        }
    }
}
