<?php

namespace App\Core\Auth\Services;

use App\Core\Auth\Contracts\AuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtAuthService implements AuthServiceInterface
{

    public function register(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'provider' => $data['provider'] ?? \App\Enums\AuthProviderEnum::EMAIL->value,
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }


    public function login(array $credentials): string|array
    {
        if (!$token = auth()->attempt($credentials)) {
            throw new \Exception('email or password is incorrect', 401);
        }

        return [
            'user'  => auth()->user(),
            'token' => $token,
        ];
    }


    public function me(): mixed
    {
        return auth()->user();
    }


    public function logout(): void
    {
        auth()->logout();
    }
}
