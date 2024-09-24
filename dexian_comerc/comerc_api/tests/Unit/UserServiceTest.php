<?php

namespace Tests\Unit;

use App\Services\UserService;
use App\Repositories\UserRepository;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    protected $userService;
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userService = new UserService($this->userRepository);
    }

    public function testGetAllUsers()
    {
        // Simulate the repository returning data
        $this->userRepository->expects($this->once())
            ->method('all')
            ->willReturn(collect(['user1', 'user2']));

        $users = $this->userService->getAllUsers();

        $this->assertCount(2, $users);
    }

    public function testCreateUserWithInvalidData()
    {
        $this->expectException(ValidationException::class);

        $data = ['name' => '', 'email' => 'invalidemail', 'password' => 'short'];

        $this->userService->createUser($data);
    }

    public function testCreateUserWithValidData()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->userRepository->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn((object) $data);

        $user = $this->userService->createUser($data);

        $this->assertEquals('John Doe', $user->name);
    }
}
