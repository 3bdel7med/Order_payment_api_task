<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function a_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name'     => ' Abdelhamed',
            'email'    => 'abdelhamed@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'user' => ['id', 'name', 'email'],
                         'token'
                     ]
                 ]);

        $this->assertDatabaseHas('users', [
            'email' => 'abdelhamed@test.com',
        ]);
    }

    /** @test */
    public function a_user_can_login()
    {
        $user = User::factory()->create([
            'email'    => 'abdelhamed@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'abdelhamed@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'user',
                         'token'
                     ]
                 ]);
    }

    /** @test */
    public function an_authenticated_user_can_get_their_profile()
    {
        $user = User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'name',
                         'email'
                     ]
                 ]);
    }

    /** @test */
    public function an_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Successfully logged out'
                 ]);
    }
}
