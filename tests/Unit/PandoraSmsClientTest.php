<?php
namespace Angstrom\Tests\Unit;

use Angstrom\PandoraSmsClient;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;

class PandoraSmsClientTest extends TestCase
{

    protected function setUp(): void
    {
        // Set up test environment variables
        putenv('PANDORA_SMS_USERNAME=testuser');
        putenv('PANDORA_SMS_PASSWORD=testpass');
    }

    protected function tearDown(): void
    {
        // Clear environment variables after each test
        putenv('PANDORA_SMS_USERNAME');
        putenv('PANDORA_SMS_PASSWORD');
        Mockery::close();
    }

    public function testConstructorWithEnvironmentVariables()
    {
        // Simulate environment variables
        putenv('PANDORA_SMS_USERNAME=testuser');
        putenv('PANDORA_SMS_PASSWORD=testpass');

        $client = new PandoraSmsClient();
        
        $this->assertNotNull($client);
    }

    public function testConstructorWithManualCredentials()
    {
        $client = new PandoraSmsClient('manualuser', 'manualpass');
        
        $this->assertNotNull($client);
    }

    public function testSendSmsSuccess()
    {
        // Mock Guzzle client
        $mockHttpClient = Mockery::mock(Client::class);
        $mockResponse = Mockery::mock(Response::class);

        $mockResponse
            ->shouldReceive('getBody->getContents')
            ->once()
            ->andReturn(json_encode(['success' => true]));

        $mockHttpClient
            ->shouldReceive('get')
            ->once()
            ->andReturn($mockResponse);

        // Use reflection to set the mocked HTTP client
        $client = new PandoraSmsClient('testuser', 'testpass');
        $reflectionProperty = new \ReflectionProperty(PandoraSmsClient::class, 'httpClient');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $mockHttpClient);

        $result = $client->sendSms(
            '+256700000000', 
            'Hello World from Angstrom', 
            'Angstrom', 
            'transactional', 
            'general'
        );

        $this->assertTrue($result['success']);
    }

    public function testSendSmsFailure()
    {
        // Mock Guzzle client to simulate a network error
        $mockHttpClient = Mockery::mock(Client::class);

        $mockHttpClient
            ->shouldReceive('get')
            ->once()
            ->andThrow(new \GuzzleHttp\Exception\RequestException(
                'Error communicating with API', 
                new \GuzzleHttp\Psr7\Request('GET', 'test')
            ));

        // Use reflection to set the mocked HTTP client
        $client = new PandoraSmsClient('testuser', 'testpass');
        $reflectionProperty = new \ReflectionProperty(PandoraSmsClient::class, 'httpClient');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $mockHttpClient);

        $result = $client->sendSms(
            '+256700000000', 
            'Hello World from Angstrom', 
            'Angstrom', 
            'transactional', 
            'general'
        );

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error_message', $result);
    }

    public function testConstructorWithMissingCredentials()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Pandora SMS credentials are required');

        // Clear environment variables
        putenv('PANDORA_SMS_USERNAME');
        putenv('PANDORA_SMS_PASSWORD');

        new PandoraSmsClient();
    }
}