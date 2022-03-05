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
use Hash;

use App\Models\User;

class AuthController extends Controller
{

    public function __construct() {
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'check']]);
        $this->middleware('license');
        $this->middleware('device');
    }

    public function index() {
        try {
            $users = User::all();
            $response = ApiResponse::Success($users, 'get user lists');
            return response()->json($response['json'], $response['status']);
            
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('unknown error');
            return response()->json($response['json'], $response['status']);
        }
    }

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

    public function register(Request $request) {
        $input = $request->only(['name', 'email', 'phone', 'password']);

        $validator = Validator::make($input, [
            'name' => 'required|string|between:2,100|unique:users',
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

    public function logout() {
        auth()->logout();
        $response = ApiResponse::Success([], 'user successfully signed out');
        return response()->json($response['json'], $response['status']);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }


    public function userProfile(Request $request) {
        $user = auth()->user();
        $response = ApiResponse::Success($user, 'login user information');
        return response()->json($response['json'], $response['status']);
    }

    public function check() {
        $user = User::all()->first();

        if(!$user) {
            $response = ApiResponse::NotFound('user not found');
            return response()->json($response['json'], $response['status']);
        }

        $response = ApiResponse::Success(['user' => $user], 'first user account');
        return response()->json($response['json'], $response['status']);
    }

    public function delete(Request $request) {
        try {

            $loggedUser = Auth::user();

            $targetUser = User::find($request->id);

            if(!$targetUser) {
                $response = ApiResponse::NotFound('user is not found');
                return response()->json($response['json'], $response['status']);
            }

            if((int) $request->id === (int) $loggedUser->id) {
                $response = ApiResponse::BedRequest('user is logged in');
                return response()->json($response['json'], $response['status']);
            }

            $deleted = User::where('id', $request->id)->delete();
            if($deleted) {
                $response = ApiResponse::Success([],'user is deleted');
                return response()->json($response['json'], $response['status']);
            }

        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('unknown error');
            return response()->json($response['json'], $response['status']);
        }
    }

    public function update(Request $request) 
    {
        $input = $request->only(['name','email', 'phone', 'active']);

        try {
            $user = User::find($request->id);

            if(!$user){
                $response = ApiResponse::NotFound('User is not found');
                return response()->json($response['json'], $response['status']);
            }

           
            $validator = Validator::make($input, [
                'name' => 'unique:users',
                'email' => 'unique:users',
                'phone' => 'unique:users'
            ]);
    
            if($validator->fails()){
                $response = ApiResponse::BedRequest($validator->errors()->first());
                return response()->json($response['json'], $response['status']);
            }

            $update = User::where('id', '=', $request->id)->update($input);
            $user->refresh();
            $response = ApiResponse::Success($user, 'user is updated');
            return response()->json($response['json'], $response['status']); 

        } catch(QueryException $e) {
            $response = ApiResponse::Unknown('unknown error');
            return response()->json($response['json'], $response['status']); 
        }
    }

    public function passwordUpdate(Request $request)
    {   
        $user = User::find($request->id);

        if(!$user) {
            $response = ApiResponse::NotFound('user is not found');
            return response()->json($response['json'], $response['status']);
        }
        $name = $user->only(['name']);
        $input = $request->only(['password']);
        $check = array_merge($input,$name);

    	$validator = Validator::make($check, [
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

        try {
            $input = $request->only(['newPassword']);
            $validator = Validator::make($input, [
                'newPassword' => 'required|string|min:6',
            ]);
            if ($validator->fails()) {
                $response = ApiResponse::BedRequest($validator->errors()->first());
                return response()->json($response['json'], $response['status']);
            }
            $newpsw = new User();
            $newpsw->password=bcrypt($input['newPassword']);
            $user = User::find($request->id);
            $user->password=$newpsw->password;
            $user->save();
            $user->refresh();
            $response = ApiResponse::Success($user, 'user password is updated');
        return response()->json($response['json'], $response['status']);   
        } catch (QueryException $e) {
            $response = ApiResponse::Unknown('unknown error');
            return response()->json($response['json'], $response['status']);
        } 

    }

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
