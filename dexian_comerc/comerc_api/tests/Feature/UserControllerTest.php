<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCanListUsers()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/users');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonCount(1);
    }

    public function testCanShowUser()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function testCannotShowNonExistentUser()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/users/999');

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
    }

    public function testCreateUserWithInvalidData()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertStatus(JsonResponse::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function testCreateUserWithValidData()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'type' => User::TYPE_CLIENT
        ]);

        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson([
                'message' => 'User created successfully!',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function testCanUpdateUser()
    {
        $user = User::factory()->create();
        $data = [
            'name' => 'New Name'
        ];

        $response = $this->actingAs($user)->putJson("/api/users/{$user->id}", $data);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'message' => 'User updated successfully.',
                'user' => ['name' => 'New Name']
            ]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function testCanDeleteUser()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
