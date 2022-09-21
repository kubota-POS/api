<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use \Carbon\Carbon;
use JWTAuth;
use Illuminate\Database\QueryException;
use Exception;
use Hash;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.verify', ['except' => ['login', 'register', 'check']]);
        $this->middleware('license', ['except' => ['check']]);
        // $this->middleware('device');
    }

    public function index(Request $request)
    {
        $pageSize = $request->pageSize ? $request->pageSize : 10;
        try {
            $users = User::orderBy('created_at', 'desc')->paginate($pageSize);
            return $this->success($users, 'get user lists');
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function login(Request $request)
    {
        $input = $request->only(['name', 'password']);

        $validator = Validator::make($input, [
            'name' => 'required',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return $this->unprocess('Incorrect username and password');
        }

        $user = auth()->user();

        if ($user['active'] === false) {
            return $this->unprocess('Account is not active');
        }

        return $this->createNewToken($token);
    }

    public function register(Request $request)
    {
        $input = $request->only(['name', 'email', 'phone', 'password']);

        $validator = Validator::make($input, [
            'name' => 'required|string|between:2,100|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }

        $input['active'] = true;
        $input['password'] = bcrypt($input['password']);

        try {
            $user = User::create($input);
            return $this->success($user, 'login user information', 201);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function logout()
    {
        auth()->logout();
        return $this->success([], 'user successfully signed out');
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }


    public function userProfile(Request $request)
    {
        $user = auth()->user();
        return $this->success([], 'login user information');
    }

    public function check()
    {
        $user = User::all()->first();

        if (!$user) {
            return $this->notFound('user not found');
        }

        return $this->success($user, 'first user account');
    }

    public function delete(Request $request)
    {
        try {

            $loggedUser = Auth::user();

            $targetUser = User::find($request->id);

            if (!$targetUser) {
                return $this->notFound('user is not found');
            }

            if ((int) $request->id === (int) $loggedUser->id) {
                return $this->unprocess('user is logged in');
            }

            $deleted = User::where('id', $request->id)->delete();
            if ($deleted) {
                return $this->success([], 'user is deleted');
            }
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function update(Request $request)
    {
        $input = $request->only(['name', 'phone', 'email', 'active']);
        try {
            $user = User::find($request->id);

            if (!$user) {
                return $this->notFound('User is not found');
            }

            $validator = Validator::make($input, [
                'name' => 'unique:users',
                'email' => 'unique:users',
                'phone' => 'unique:users'
            ]);

            if ($validator->fails()) {
                return $this->unprocess($validator->errors()->first());
            }

            $update = User::where('id', '=', $request->id)->update($input);
            $user->refresh();
            return $this->success($user, 'user is updated', 201);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    public function passwordUpdate(Request $request)
    {
        $user = User::find($request->id);

        if (!$user) {
            return $this->notFound('user is not found');
        }

        $name = $user->only(['name']);
        $input = $request->only(['password']);
        $check = array_merge($input, $name);

        $validator = Validator::make($check, [
            'name' => 'required',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->unprocess($validator->errors()->first());
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return $this->unprocess('current password does not match');
        }

        try {
            $input = $request->only(['newPassword']);
            $validator = Validator::make($input, [
                'newPassword' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return $this->unprocess($validator->errors()->first());
            }

            $newpsw = new User();
            $newpsw->password = bcrypt($input['newPassword']);
            $user = User::find($request->id);
            $user->password = $newpsw->password;
            $user->save();
            $user->refresh();
            return $this->success($user, 'user password is updated', 201);
        } catch (QueryException $e) {
            return $this->unknown();
        }
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'success' => true,
            'message' => 'login success',
            'data' => [
                'account' => auth()->user(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 1
            ]
        ]);
    }
}
