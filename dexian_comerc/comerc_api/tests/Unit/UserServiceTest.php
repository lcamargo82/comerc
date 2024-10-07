<?php

namespace Tests\Unit;

use App\Services\UserService;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

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

    public function testGetUserReturnsUser()
    {
        $userId = 1;
        $expectedUser = ['id' => $userId, 'name' => 'Test User'];

        $this->userRepository->method('find')->willReturn($expectedUser);

        $user = $this->userService->getUser($userId);

        $this->assertEquals($expectedUser, $user);
    }

    public function testReturnUserWhenFound()
    {
        $user = (object) ['id' => 1, 'name' => 'John Doe'];

        $this->userRepository->method('find')
            ->with(1)
            ->willReturn($user);

        $result = $this->userService->getUser(1);

        $this->assertEquals($user, $result);
    }

    public function testThrowExceptionWhenUserNotFound()
    {
        $this->userRepository->method('find')
            ->with(999)
            ->willReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Client not found");
        $this->expectExceptionCode(JsonResponse::HTTP_NOT_FOUND);

        $this->userService->getUser(999);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
