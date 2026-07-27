<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function register(Request $request)
    {
        $result = $this->authService->register($request);

        return (new UserResource($result['user']))
            ->additional([
                'meta' => [
                    'token'   => $result['token'],
                    'message' => 'User registered successfully.'
                ]
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function login(Request $request)
    {
        $result = $this->authService->login($request);

        return (new UserResource($result['user']))
            ->additional([
                'meta' => [
                    'token'   => $result['token'],
                    'message' => 'Logged in successfully.'
                ]
            ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return response()->json([
            'message' => 'Logged out successfully.'
        ]);
    }
}
