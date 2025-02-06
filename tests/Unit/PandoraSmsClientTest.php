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
            ->andReturn(json_encode([
                'statusCode' => 201,
                'success' => true,
                'messages' => ['Message sent successfully'],
                'data' => [
                    'supported_contacts' => 1,
                    'unsupported_contacts' => 0,
                    'sms_cost' => 25,
                    'balance' => 9430
                ]
            ]));

        $mockHttpClient
            ->shouldReceive('post')
            ->once()
            ->withArgs(function ($url, $params) {
                return $url === 'https://www.sms.thepandoranetworks.com/API/send_sms/' &&
                    isset($params['form_params']) &&
                    $params['form_params']['message_type'] === 'non_customised' &&
                    $params['form_params']['message_category'] === 'bulk';
            })
            ->andReturn($mockResponse);

        // Use reflection to set the mocked HTTP client
        $client = new PandoraSmsClient('testuser', 'testpass');
        $reflectionProperty = new \ReflectionProperty(PandoraSmsClient::class, 'httpClient');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $mockHttpClient);

        $result = $client->sendSms(
            '0700000000', 
            'Hello World from Angstrom', 
            'Angstrom', 
            'non_customised', 
            'bulk'
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(201, $result['statusCode']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals(25, $result['data']['sms_cost']);
    }

    public function testSendSmsFailure()
    {
        // Mock Guzzle client to simulate a network error
        $mockHttpClient = Mockery::mock(Client::class);

        $mockHttpClient
            ->shouldReceive('post')
            ->once()
            ->andThrow(new \GuzzleHttp\Exception\RequestException(
                'Error communicating with API', 
                new \GuzzleHttp\Psr7\Request('POST', 'test')
            ));

        // Use reflection to set the mocked HTTP client
        $client = new PandoraSmsClient('testuser', 'testpass');
        $reflectionProperty = new \ReflectionProperty(PandoraSmsClient::class, 'httpClient');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($client, $mockHttpClient);

        $result = $client->sendSms(
            '0700000000', 
            'Hello World from Angstrom', 
            'Angstrom', 
            'non_customised', 
            'bulk'
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