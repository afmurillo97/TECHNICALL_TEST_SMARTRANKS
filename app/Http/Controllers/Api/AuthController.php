<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="User registration, login, and token management",
 * )
*/
class AuthController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Authentication"},
     *     summary="Get authenticated user details",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User in Database",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message", 
     *                 type="string", 
     *                 example="User retrieved successfully"
     *             ),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     ref="#/components/schemas/User"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function getUser(Request $request): JsonResponse 
    {
        return $this->successResponse(
            'User retrieved successfully',
            200,
            ['data' => auth()->user()->makeHidden(['created_at', 'updated_at', 'email_verified_at'])]
        );
    }
    
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","role"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="user@smartranks.test"),
     *             @OA\Property(property="password", type="string", format="password", example="SecurePassword123"),
     *             @OA\Property(property="role", type="string", enum={"user","admin"}, example="user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         example="User created successfully"
     *                     )
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="response",
     *                         @OA\Property(
     *                             property="data",
     *                             type="object",
     *                             @OA\Property(property="role", type="string", example="user"),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="email", type="string", example="user@smartranks.test"),
     *                             @OA\Property(property="updated_at", type="integer", example="2025-05-16T20:06:41.000000Z"),
     *                             @OA\Property(property="created_at", type="integer", example="2025-05-16T20:06:41.000000Z"),
     *                             @OA\Property(property="id", type="integer", example=1)
     *                         )
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid Credentials",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *        response=422,
     *        description="The user already exists in the database",
     *        @OA\JsonContent(
     *            @OA\Property(
     *                property="message",
     *                type="string",
     *                example="The email has already been taken."
     *            ),
     *            @OA\Property(
     *                property="errors",
     *                type="object",
     *                @OA\Property(
     *                    property="email",
     *                    type="array",
     *                    @OA\Items(
     *                        type="string",
     *                        example="The email has already been taken."
     *                    )
     *                )
     *            )
     *        )
     *    )
     * )
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role
        ]);

        $response = [
            'data' => $user->makeHidden(['password', 'created_at', 'updated_at'])
        ];

        return $this->successResponse(
            'User created successfully',
            201,
            $response
        ); 
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Authenticate user and obtain JWT token",
     *     operationId="authLogin",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User credentials",
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(
     *                 property="email", 
     *                 type="string", 
     *                 format="email", 
     *                 example="user@smartranks.test"
     *             ),
     *             @OA\Property(
     *                 property="password", 
     *                 type="string", 
     *                 format="password", 
     *                 example="SecurePassword123",
     *                 minLength=8
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Authentication successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message", 
     *                 type="string", 
     *                 example="Welcome John Doe!!!"
     *             ),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     ref="#/components/schemas/User"
     *                 ),
     *                 @OA\Property(
     *                     property="token_type", 
     *                     type="string", 
     *                     example="Bearer"
     *                 ),
     *                 @OA\Property(
     *                     property="access_token", 
     *                     type="string", 
     *                     example="1|bu3jPwcyNPb8d14MEc9BPNhw6JSWUA2yQJIVPZ82ffb4e13"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid Credentials",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="error", 
     *                 type="string", 
     *                 example="Unauthorized"
     *             )
     *         )
     *     )
     * )
     */
    public function login(LoginUserRequest $request): JsonResponse 
    {
        if(!Auth::attempt($request->only('email', 'password'))){
            return $this->errorResponse('Unauthorized', 401); 
        }

        $user = User::where('email', $request['email'])
            ->firstOrFail()
            ->makeHidden(['created_at', 'updated_at', 'email_verified_at']);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            'Welcome ' . $user->name . '!!!', 
            200,
            ['data' => $user, 'token_type' => 'Bearer', 'access_token' => $token]
        ); 
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Revoke all access tokens",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message", 
     *                 type="string", 
     *                 example="You have successfully logged out. All access tokens have been revoked."
     *             ),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                    @OA\Property(property="logout_time", type="string", format="date-time")       
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     */
    public function logout(): JsonResponse 
    {
        auth()->user()->tokens()->delete();

        return  $this->successResponse(
            'You have successfully logged out. All access tokens have been revoked.', 
            200,
            ['logout_time' => now()->toDateTimeString()]
        );
    }
}
