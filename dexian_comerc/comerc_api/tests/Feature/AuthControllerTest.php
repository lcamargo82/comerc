<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function testRegisterUserSuccessfully()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'type' => 2,
        ];

        $response = $this->postJson('api/register', $data);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'type',
                    'created_at',
                    'updated_at',
                ],
                'message'
            ]);
    }

    public function testRegisterUserWithValidationError()
    {
        $data = [
            'name' => 'John Doe',
            'password' => 'password',
            'password_confirmation' => 'password',
            'type' => 2,
        ];

        $response = $this->postJson('api/register', $data);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'The email field is required.',
            ]);
    }

    public function testCannotRegisterWithDuplicateEmail()
    {
        User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password'),
        ]);

        $data = [
            'name' => 'Jane Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'type' => 2,
        ];

        $response = $this->postJson('api/register', $data);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'The email has already been taken.',
            ]);
    }

    public function testLoginUserSuccessfully()
    {
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password')
        ]);

        $data = [
            'email' => 'johndoe@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson('api/login', $data);

        $response->assertStatus(JsonResponse::HTTP_OK)
            ->assertJsonStructure([
                'token'
            ]);

        $responseData = $response->json();
        $this->assertTrue(isset($responseData['token']));
        $this->assertStringStartsWith('1|', $responseData['token']);
    }

    public function testLoginWithInvalidCredentials()
    {
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password')
        ]);

        $data = [
            'email' => 'johndoe@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('api/login', $data);

        $response->assertStatus(JsonResponse::HTTP_BAD_REQUEST)
            ->assertJson([
                'message' => 'The provided credentials are incorrect.'
            ]);
    }
}
