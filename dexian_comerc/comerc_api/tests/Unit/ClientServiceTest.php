<?php

namespace Tests\Unit\Services;

use App\Models\Client;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ClientServiceTest extends TestCase
{
    protected $clientRepository;
    protected $clientService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientRepository = Mockery::mock(ClientRepository::class);
        $this->clientService = new ClientService($this->clientRepository);
    }

    public function testGetAllClientsReturnsCollection()
    {
        $clients = collect([new Client(), new Client()]);

        $this->clientRepository->shouldReceive('all')->once()->andReturn($clients);

        $result = $this->clientService->getAllClients();

        $this->assertEquals($clients, $result);
    }

    public function testGetClientReturnsClient()
    {
        $client = new Client();
        $this->clientRepository->shouldReceive('find')->with(1)->once()->andReturn($client);

        $result = $this->clientService->getClient(1);

        $this->assertEquals($client, $result);
    }

    public function testGetClientThrowsExceptionWhenNotFound()
    {
        $this->clientRepository->shouldReceive('find')->with(1)->once()->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Client not found');

        $this->clientService->getClient(1);
    }

    public function testCreateClientThrowsValidationExceptionForInvalidData()
    {
        $data = [
            'user_id' => null,
            'phone' => '123',
        ];

        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'phone' => 'required|string|max:15',
        ]);

        Validator::shouldReceive('make')->once()->andReturn($validator);

        $this->expectException(ValidationException::class);

        $this->clientService->createClient($data);
    }

    public function testUpdateClientThrowsExceptionWhenNotFound()
    {
        $this->clientRepository->shouldReceive('find')->with(1)->once()->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Client not found');

        $this->clientService->updateClient(1, []);
    }

    public function testDeleteClientDeletesClient()
    {
        $client = new Client(['id' => 1]);

        $this->clientRepository->shouldReceive('find')->with(1)->once()->andReturn($client);
        $this->clientRepository->shouldReceive('delete')->with($client->id)->once()->andReturn(true);

        $result = $this->clientService->deleteClient(1);

        $this->assertTrue($result);
    }

    public function testDeleteClientThrowsExceptionWhenNotFound()
    {
        $this->clientRepository->shouldReceive('find')->with(1)->once()->andReturn(null);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Client not found');

        $this->clientService->deleteClient(1);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
