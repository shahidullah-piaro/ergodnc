<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as RulesPassword;

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

        $tString = Hash::make($request->email . $request->password);

        return response($this->createAuthToken($user, $tString), 200);
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

        $user = Auth::user();

        $tString = Hash::make($request->email . $request->password);

        return response($this->createAuthToken($user, $tString), 200);

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


    function sendResetLink(Request $request)
    {
        // 
        $request->validate(['email' => 'required|email:rfc,dns']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            $responseMessage = [
                'statusCode' => 200,
                'status' => 'Success',
                'message' => 'Password reset link sent to the email address.',
            ];

            return response($responseMessage);
        } else {

            $responseMessage = [
                'statusCode' => 422,
                'status' => 'Error',
                'errors' => ['email' => ['The email address do not match our records.'],],

            ];

            return response($responseMessage, 422);
        }
    }


    function resetPassword(Request $request)
    { 
        $request->validate([
            'token' => 'required',
            'email' => ['required', 'email:rfc,dns'],
            'password' => [
                'required', 'confirmed',
                RulesPassword::min(8)->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
        ]);


        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );



        if ($status === Password::PASSWORD_RESET) {
            $responseMessage = [
                'statusCode' => 200,
                'status' => 'Success',
                'message' => 'Password reset successfully. Please, login again.'
            ];

            return response($responseMessage);
        } else if ($status === Password::INVALID_TOKEN) {

            $responseMessage = [
                'statusCode' => 422,
                'status' => 'Error',
                'errors' => ['token' => ['Invalid reset token.'],],
            ];

            return response($responseMessage, 422);
        } else {

            $responseMessage = [
                'statusCode' => 422,
                'status' => 'Error',
                'errors' => ['email' => ['The email address do not match our records.'],],
            ];

            return response($responseMessage, 422);
        }
    }


    public function me(Request $request)
    {
        return $request->user();
    }


    private function createAuthToken($user, $tokenString)
    {
        $responseMessage = [
            'statusCode' => 200,
            'status' => 'Success',
            "id" => (string)$user->id,
            "name" => $user->name,
            "email" => $user->email,
            'token' => $user->createToken($tokenString, ['create', 'update'], now()->addDays(180))->plainTextToken,
        ];

        return $responseMessage;
    }
}
