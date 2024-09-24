<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCanListUsers()
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function testCanShowUser()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'name' => $user->name,
                'email' => $user->email,
            ]);
    }

    public function testCannotShowNonExistentUser()
    {
        $response = $this->getJson('/api/users/999');

        $response->assertStatus(404);
    }

    public function testCanUpdateUser()
    {
        $user = User::factory()->create();
        $data = ['name' => 'New Name'];

        $response = $this->putJson("/api/users/{$user->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User updated successfully.',
                'user' => ['name' => 'New Name'],
            ]);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function testCanDeleteUser()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }
}
