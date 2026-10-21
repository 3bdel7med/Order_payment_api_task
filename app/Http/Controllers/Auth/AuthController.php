<?php

namespace App\Http\Controllers\Auth;

use App\Core\Auth\Factories\AuthServiceFactory;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AuthController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['login', 'register']),
            new Middleware('throttle:10,1', only: ['login', 'register'])
        ];
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $provider = $request->input('provider', 'email');

        $authService = AuthServiceFactory::make($provider);

        $result = $authService->register($request->validated());

        return response()->json([
            'message' => 'Account created successfully',
            'data'    => [
                'user'  => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            // if you want to support multiple providers, you can pass the provider in the request. For now, we'll default to 'email'.
            $provider = $request->input('provider', 'email');

            // get the appropriate AuthService based on the provider
            $authService = AuthServiceFactory::make($provider);

            $result = $authService->login($request->validated());

            return response()->json([
                'message' => 'Login successful',
                'data'    => [
                    'user'  => new UserResource($result['user']),
                    'token' => $result['token'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    public function me(): JsonResponse
    {
        // default provider is email, but you can modify this to accept a provider parameter if needed
        $authService = AuthServiceFactory::make('email');

        return response()->json([
            'data' => new UserResource($authService->me())
        ]);
    }

    public function logout(): JsonResponse
    {
        $authService = AuthServiceFactory::make('email');
        $authService->logout();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
