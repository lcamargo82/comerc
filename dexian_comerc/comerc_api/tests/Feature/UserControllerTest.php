<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function testCanListUsers()
    {
        $user = User::factory()->create();

        $users = User::factory()->count(1)->make();

        $userService = $this->mock(UserService::class);

        $userService->shouldReceive('getAllUsers')
            ->once()
            ->andReturn($users);

        $response = $this->actingAs($user)->getJson('/api/users');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonCount(1);
    }

    public function testIndexHandlesException()
    {
        $userServiceMock = $this->createMock(UserService::class);

        $userServiceMock->expects($this->once())
            ->method('getAllUsers')
            ->will($this->throwException(new \Exception('Erro ao buscar usuários', 500)));

        $controller = new UserController($userServiceMock);

        $response = $controller->index();

        $this->assertEquals(500, $response->status());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'Erro ao buscar usuários']),
            $response->getContent()
        );
    }

    public function testCanShowUser()
    {
        $user = User::factory()->create();

        $userService = $this->mock(UserService::class);


        $userService->shouldReceive('getUser')
            ->once()
            ->with($user->id)
            ->andReturn($user);

        $response = $this->actingAs($user)->getJson("/api/users/{$user->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson([
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function testCanShowNonExistentUser()
    {
        $user = User::factory()->create();

        $userService = $this->mock(UserService::class);

        $userService->shouldReceive('getUser')
            ->once()
            ->with(999)
            ->andThrow(new \Exception('User not found', JsonResponse::HTTP_NOT_FOUND));

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

    public function testLoginWithMissingEmail()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['email']);
    }

    public function testLoginWithInvalidEmailFormat()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email-format',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['email']);
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

    public function testCanUpdateNonExistentUser()
    {
        $userService = $this->mock(UserService::class);

        $userService->shouldReceive('updateUser')
            ->with(1, ['name' => 'New Name'])
            ->andThrow(new \Exception('Error updating user', JsonResponse::HTTP_NOT_FOUND));

        $response = $this->json('PUT', '/users/1', ['name' => 'New Name']);

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'The route users/1 could not be found.',
        ]);
    }

    public function testCanDeleteUser()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function testCanDeleteNonExistentUser()
    {
        $userService = $this->mock(UserService::class);

        $userService->shouldReceive('deleteUser')
            ->with(1)
            ->andThrow(new \Exception('Error deleting user', JsonResponse::HTTP_NOT_FOUND));

        $response = $this->json('DELETE', '/users/1');

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND);
        $response->assertJson([
            'message' => 'The route users/1 could not be found.',
        ]);
    }
}
