<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Exception;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $input = $request->validated();

        $creds = [
            'email' => $input['email'],
            'password' => $input['password'],
        ];

        if (!$token = auth()->attempt($creds)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create($request->all());
            if (!$user) {
                throw new Exception("User not created!");
            }

            return response()->json(
                [
                    'error' => false,
                    'user' => $user,
                    'message' => 'Created!'
                ],
                201
            );
        } catch (Exception $error) {
            return response()->json([
                'error' => true,
                'message' => $error->getMessage()
            ]);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
