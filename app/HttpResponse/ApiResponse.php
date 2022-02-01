<?php
namespace App\HttpResponse;

class ApiResponse {

    public function Success($data, $message) {
        return [
            "json" => [
                'success' => true,
                'message' => $message ? $message : 'success',
                'data' => $data
            ],
            "status" => 200
        ];
    }

    public function Created($data, $message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'success',
                'data' => $data
            ],
            "status" => 201
        ];
    }

    public function NotFound($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'resource not found'
            ],
            "status" => 404
        ];
    }

    public function BedRequest($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'bed request'
            ],
            "status" => 400
        ];
    }

    public function UnProcess($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'can not process'
            ],
            "status" => 422
        ];
    }

    public function Unknown($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'unknown error'
            ],
            "status" => 500
        ];
    }

    public function Unauthorized($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'unauthorized'
            ],
            "status" => 401
        ];
    }
}