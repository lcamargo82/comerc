<?php

namespace Tests\Unit;

use App\Services\UserService;
use App\Repositories\UserRepository;
use Mockery;
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
        $this->userRepository->expects($this->once())
            ->method('all')
            ->willReturn(collect(['user1', 'user2']));

        $users = $this->userService->getAllUsers();

        $this->assertCount(2, $users);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
