<?php

namespace App\Core\Auth\Factories;

use App\Core\Auth\Contracts\AuthServiceInterface;
use App\Core\Auth\Services\JwtAuthService;
use App\Enums\AuthProviderEnum;
use InvalidArgumentException;

class AuthServiceFactory
{
    public static function make(string $provider): AuthServiceInterface
    {
        $authProvider = AuthProviderEnum::tryFrom($provider);

        return match ($authProvider) {
            AuthProviderEnum::EMAIL => app(JwtAuthService::class),
            // AuthProviderEnum::GOOGLE => app(GoogleAuthService::class),
            default => throw new InvalidArgumentException("  un supported [{$provider}]   ."),
        };
    }
}
