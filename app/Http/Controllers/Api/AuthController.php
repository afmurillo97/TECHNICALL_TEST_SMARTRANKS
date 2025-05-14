<?php

namespace App\Http\Controllers\APi;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use \stdClass;

class AuthController extends BaseController
{
    public function getUser(Request $request): JsonResponse 
    {
        return $this->successResponse(
            'User retrieved successfully',
            ['data' => auth()->user()],
            200
        );
    }
    
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role
        ]);

        $response = [
            'data' => $user->makeHidden(['password'])
        ];

        return $this->successResponse(
            'User created successfully',
            $response,
            200
        ); 
    }

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
            ['data' => $user, 'token_type' => 'Bearer', 'access_token' => $token],
            200
        ); 
    }

    public function logout(): JsonResponse 
    {
        auth()->user()->tokens()->delete();

        return  $this->successResponse(
            'You have successfully logged out. All access tokens have been revoked.', 
            ['logout_time' => now()->toDateTimeString()],
            200
        );
    }
}
