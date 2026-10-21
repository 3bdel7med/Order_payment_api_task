<?php
namespace App\Core\Auth\Contracts;
interface AuthServiceInterface
{
    public function register(array $data): array;
    public function login(array $credentials):string | array;
    public function logout():void;
    public function me():mixed;
}
