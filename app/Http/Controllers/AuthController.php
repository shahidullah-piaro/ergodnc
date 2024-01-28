<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(SignupRequest $request)
    {
        $data = $request->validated();

        /** @var \App\Models\User $user */
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password'])
        ]);

        //$adminToken = $user->createToken('admin-token',['create', 'update', 'delete']);

        // return $this->success([
        //     'user' => $user,
        //     'admin-token' => $adminToken->plainTextToken,
        //     'user-token' => $userToken->plainTextToken,
        // ]);

        $userToken = $user->createToken('user-token',['create', 'update']);
        $token = $userToken->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $remember = $credentials['remember'] ?? false;
        unset($credentials['remember']);

        if (!Auth::attempt($credentials, $remember)) {
            return response([
                'message' => 'The Provided credentials are not correct'
            ], 422);
        }


        //$adminToken = $user->createToken('admin-token',['create', 'update', 'delete']);

        // return $this->success([
        //     'user' => $user,
        //     'admin-token' => $adminToken->plainTextToken,
        //     'user-token' => $userToken->plainTextToken,
        // ]);


        $user = Auth::user();
        $userToken = $user->createToken('user-token',['create', 'update']);
        $token = $userToken->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        // Revoke the token that was used to authenticate the current request...
        $user->currentAccessToken()->delete();

        return response([
            'success' => true
        ]);
    }

    public function me(Request $request)
    {
        return $request->user();
    }
}
