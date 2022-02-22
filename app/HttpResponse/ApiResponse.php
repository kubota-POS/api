<?php
namespace App\HttpResponse;

class ApiResponse {

    public static function Success($data, $message) {
        return [
            "json" => [
                'success' => true,
                'message' => $message ? $message : 'success',
                'data' => $data
            ],
            "status" => 200
        ];
    }

    public static function Created($data, $message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'success',
                'data' => $data
            ],
            "status" => 201
        ];
    }

    public static function NotFound($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'resource not found'
            ],
            "status" => 404
        ];
    }

    public static function BedRequest($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'bed request'
            ],
            "status" => 400
        ];
    }

    public static function UnProcess($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'can not process'
            ],
            "status" => 422
        ];
    }

    public static function Unknown($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'unknown error'
            ],
            "status" => 500
        ];
    }

    public static function Unauthorized($message) {
        return [
            "json" => [
                'success' => false,
                'message' => $message ? $message : 'unauthorized'
            ],
            "status" => 401
        ];
    }
}