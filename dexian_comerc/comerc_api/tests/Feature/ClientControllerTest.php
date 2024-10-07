<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotListClientsWithoutAuthentication()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function testCannotAccessClientListWhenNotAuthenticated()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(JsonResponse::HTTP_UNAUTHORIZED)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function testCanCreateClient()
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'phone' => '1234567890',
            'birth_date' => '1990-01-01',
            'address' => '123 Main St',
            'complement' => 'Apt 4B',
            'district' => 'Downtown',
            'zipcode' => '12345',
        ];

        $response = $this->actingAs($user)->postJson('/api/clients', $data);

        $response->assertStatus(JsonResponse::HTTP_CREATED)
            ->assertJson(['message' => 'Client created successfully.']);
        $this->assertDatabaseHas('clients', $data);
    }

    public function testCanGetAllClients()
    {
        $user = User::factory()->create();

        Client::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/clients');

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonCount(3);
    }

    public function testCanGetSingleClient()
    {
        $user = User::factory()->create();

        $client = Client::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/clients/' . $client->id);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['id' => $client->id]);
    }

    public function testCanUpdateClient()
    {
        $user = User::factory()->create();

        $client = Client::factory()->create();

        $data = [
            'user_id' => $user->id,
            'phone' => '0987654321',
            'address' => '456 Elm St',
            'district' => 'Uptown',
            'zipcode' => '09170023'
        ];

        $response = $this->actingAs($user)->putJson('/api/clients/' . $client->id, $data);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'Client updated successfully.']);
        $this->assertDatabaseHas('clients', array_merge(['id' => $client->id], $data));
    }

    public function testCanDeleteClient()
    {
        $user = User::factory()->create();

        $client = Client::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/clients/' . $client->id);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJson(['message' => 'Client deleted successfully.']);
        $this->assertSoftDeleted('clients', ['id' => $client->id]);
    }

    public function testCreateClientWithInvalidData()
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => '',
            'phone' => '1234567890123456',
        ];

        $response = $this->actingAs($user)->postJson('/api/clients', $data);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJsonStructure(['message']);
    }

    public function testCannotGetNonExistentClient()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/clients/999');

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(['message' => 'Client not found']);
    }

    public function testCannotUpdateNonExistentClient()
    {
        $user = User::factory()->create();

        $data = [
            'phone' => '1234567890',
            'address' => 'New Address',
        ];

        $response = $this->actingAs($user)->putJson('/api/clients/999', $data);

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(['message' => 'Client not found']);
    }

    public function testCannotDeleteNonExistentClient()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/clients/999');

        $response->assertStatus(JsonResponse::HTTP_NOT_FOUND)
            ->assertJson(['message' => 'Client not found']);
    }
}
