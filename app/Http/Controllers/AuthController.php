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
     * @OA\POST(
     *  tags={"JWT Authentication"},
     *  summary="Get a autentication user token",
     *  description="This endpoints return a new token user authentication for use on protected endpoints",
     *  path="/api/login",
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", example="jeferson@email.com"),
     *              @OA\Property(property="password", type="string", example="password")
     *          )
     *      ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="Token generated",
     *    @OA\JsonContent(
     *       @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0L2FwaS9sb2dpbiIsImlhdCI6MTcyNDEyNTA2OSwiZXhwIjoxNzI0MTI4NjY5LCJuYmYiOjE3MjQxMjUwNzAsImp0aSI6ImFUY1JnV010d004aGVVbzgiLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.IIElpk0R-CirY8fO4svp6aJR6a1BUeCLS5C5ayPQVqo")
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Incorrect credentials",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *       @OA\Property(property="errors", type="string", example="..."),
     *    )
     *  )
     * )
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

    /**
     * @OA\POST(
     *  tags={"JWT Authentication"},
     *  summary="Get a autentication user token",
     *  description="This endpoints return a new token user authentication for use on protected endpoints",
     *  path="/api/register",
     *  @OA\RequestBody(
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              required={"name", "email","password"},
     *              @OA\Property(property="name", type="string", example="jeferson"),
     *              @OA\Property(property="email", type="string", example="jeferson@email.com"),
     *              @OA\Property(property="password", type="string", example="password")
     *          )
     *      ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="User created!"
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Incorrect credentials",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The provided credentials are incorrect."),
     *       @OA\Property(property="errors", type="string", example="..."),
     *    )
     *  )
     * )
     */
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
     * @OA\GET(
     *  tags={"JWT Authentication"},
     *  summary="Details user logged!",
     *  description="This endpoint returns details of user logged!.",
     *  path="/api/me",
     *  security={ {"bearerToken":{}} },
     *  @OA\Response(
     *    response=200,
     *    description="Details user logged!",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Details user logged!")
     *    )
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated"),
     *    )
     *  )
     * )
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * @OA\GET(
     *  tags={"JWT Authentication"},
     *  summary="Revoke all user tokens",
     *  description="This endpoint provides a logout for user, revoking all actived user tokens.",
     *  path="/api/logout",
     *  security={ {"bearerToken":{}} },
     *  @OA\Response(
     *    response=200,
     *    description="All user tokens revoked",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="All user tokens were revoked !")
     *    )
     *  ),
     *  @OA\Response(
     *    response=401,
     *    description="Unauthenticated",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated"),
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="Incorrect fields",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="The email field is required."),
     *       @OA\Property(property="errors", type="string", example="..."),
     *    )
     *  )
     * )
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
