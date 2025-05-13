<?php

namespace App\Http\Controllers\APi;

use App\Http\Controllers\Api\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            'User created successfully',
            ['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer'],
            200
        ); 
    }

    public function login(Request $request): JsonResponse 
    {
        if(!Auth::attempt($request->only('email', 'password'))){
            return $this->errorResponse('Unauthorized', 401); 
        }

        $user = User::where('email', $request['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse(
            'Welcome ' . $user->name . '!!!', 
            ['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer'],
            200
        ); 
    }

    public function logout(): JsonResponse 
    {
        auth()->user()->tokens()->delete();

        return  $this->successResponse(
            'You have successfully logged out and the token was successfully deleted!', 
            null,
            200
        );
    }
}
