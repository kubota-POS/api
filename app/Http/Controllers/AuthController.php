<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use \Carbon\Carbon;
use Validator;
use JWTAuth;
use App\HttpResponse\ApiResponse;
use Illuminate\Database\QueryException;
use Exception;

use App\Models\User;

class AuthController extends Controller
{
    /**
    * Create a new AuthController instance.
    */
    public function __construct() {
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'check']]);
        $this->middleware('license');
    }

    /**
    * Get a JWT via given credentials.
    */
    public function login(Request $request){
        $input = $request->only(['name', 'password']);

    	$validator = Validator::make($input, [
            'name' => 'required',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            $response = ApiResponse::Unauthorized('Unauthorized');
            return response()->json($response['json'], $response['status']);
        }

        $user = auth()->user();

        if($user['active'] === false) {
            $response = ApiResponse::Unprocess('Account is not active');
            return response()->json($response['json'], $response['status']);
        }

        return $this->createNewToken($token);
    }

    /**
    * Register a User.
    */
    public function register(Request $request) {
        $input = $request->only(['name', 'email', 'phone', 'password']);

        $validator = Validator::make($input, [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            $response = ApiResponse::BedRequest($validator->errors()->first());
            return response()->json($response['json'], $response['status']);
        }

        $input['active'] = true;
        $input['password'] = bcrypt($input['password']);

        try {
            $user = User::create($input);

            $response = ApiResponse::Created(['user' => $user], 'login user information');
            return response()->json($response['json'], $response['status']);

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('unknown error');
            return response()->json($response['json'], $response['status']);
        }

    }

    /**
    * Log the user out (Invalidate the token).
    */
    public function logout() {
        auth()->logout();
        $response = ApiResponse::Success([], 'user successfully signed out');
        return response()->json($response['json'], $response['status']);
    }

    /**
    * Refresh a token.
    */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
    */
    public function userProfile(Request $request) {
        $user = auth()->user();
        $response = ApiResponse::Success($user, 'login user information');
        return response()->json($response['json'], $response['status']);
    }

    /**
     * Check user exist for first time
    */
    public function check() {
        $user = User::all()->first();

        if(!$user) {
            $response = ApiResponse::NotFound('user not found');
            return response()->json($response['json'], $response['status']);
        }

        $response = ApiResponse::Success(['user' => $user], 'first user account');
        return response()->json($response['json'], $response['status']);
    }

    /**
    * Get the token array structure.
    */
    protected function createNewToken($token){
        return response()->json([
            'success' => true,
            'message' => 'login success',
            'data' => [
                'account' => auth()->user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        ]);
    }

}
