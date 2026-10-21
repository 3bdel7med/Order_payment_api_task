<?php

namespace App\Enums;

enum AuthProviderEnum: string
{
    case EMAIL = 'email';
    case GOOGLE = 'google';
    case GITHUB = 'github';
    case PHONE = 'phone';
}
