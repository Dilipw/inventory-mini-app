<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('message', 'Registration successful.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                ],
            ])
            ->assertJsonPath('data.user.role', 'staff');

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'staff',
        ]);

        $this->assertNotEmpty(
            $response->json('data.token')
        );
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
            ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'Password@123',
            'password_confirmation' => 'WrongPassword@123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password@123',
            'role' => 'staff',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                    'token',
                ],
            ])
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'test@example.com');

        $this->assertNotEmpty(
            $response->json('data.token')
        );
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword@123',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_cannot_login_with_non_existing_email(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'notfound@example.com',
            'password' => 'Password@123',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_requires_valid_data(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'manager',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response
            ->assertStatus(200)
            ->assertJsonPath('message', 'Profile fetched successfully.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
            ])
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.role', 'manager');
    }

    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response
            ->assertStatus(401);
    }
    public function test_user_can_logout_and_token_is_invalidated(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $token = $loginResponse->json('data.token');

        $this->app['auth']->forgetGuards();

        $logoutResponse = $this->withToken($token)
            ->postJson('/api/logout');

        $logoutResponse
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful.',
            ]);

        $this->app['auth']->forgetGuards();

        $profileResponse = $this->withToken($token)
            ->getJson('/api/profile');

        $profileResponse
            ->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->withToken($token)
            ->putJson('/api/change-password', [
                'current_password' => 'Password@123',
                'password' => 'NewPassword@123',
                'password_confirmation' => 'NewPassword@123',
            ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Password changed successfully. Please login again.',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'NewPassword@123',
        ]);

        $loginResponse
            ->assertStatus(200)
            ->assertJsonPath('message', 'Login successful.');

        $this->assertNotEmpty(
            $loginResponse->json('data.token')
        );
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/change-password', [
            'current_password' => 'WrongPassword@123',
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'current_password',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_change_password_requires_valid_data(): void
    {
        $user = User::factory()->create([
            'password' => 'Password@123',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/change-password', [
            'current_password' => '',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'current_password',
                'password',
            ]);
    }

    public function test_unauthenticated_user_cannot_change_password(): void
    {
        $response = $this->putJson('/api/change-password', [
            'current_password' => 'Password@123',
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertStatus(401);
    }

    public function test_old_password_cannot_be_used_after_password_change(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/change-password', [
            'current_password' => 'Password@123',
            'password' => 'NewPassword@123',
            'password_confirmation' => 'NewPassword@123',
        ]);

        $response->assertStatus(200);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $loginResponse
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials.',
            ]);
    }

    public function test_old_token_is_invalidated_after_password_change(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        $token = $loginResponse->json('data.token');

        $this->app['auth']->forgetGuards();

        $response = $this->withToken($token)
            ->putJson('/api/change-password', [
                'current_password' => 'Password@123',
                'password' => 'NewPassword@123',
                'password_confirmation' => 'NewPassword@123',
            ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Password changed successfully. Please login again.',
            ]);

        $this->app['auth']->forgetGuards();

        $profileResponse = $this->withToken($token)
            ->getJson('/api/profile');

        $profileResponse->assertStatus(401);
    }

    public function test_change_password_requires_password_confirmation(): void
    {
        $user = User::factory()->create([
            'password' => 'Password@123',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/change-password', [
            'current_password' => 'Password@123',
            'password' => 'NewPassword@123',
            'password_confirmation' => 'DifferentPassword@123',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'password',
            ]);
    }
}
