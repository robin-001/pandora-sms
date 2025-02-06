<?php
namespace Angstrom;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PandoraSmsClient
{
    private string $baseUrl;
    private Client $httpClient;
    private string $username;
    private string $password;

    public function __construct(?string $username = null, ?string $password = null, ?string $baseUrl = null)
    {
        // Use getenv() instead of env()
        $this->username = $username ?? getenv('PANDORA_SMS_USERNAME') ?: '';
        $this->password = $password ?? getenv('PANDORA_SMS_PASSWORD') ?: '';
        $this->baseUrl = $baseUrl ?? getenv('PANDORA_SMS_BASE_URL') ?: 'https://www.sms.thepandoranetworks.com/API/send_sms/';

        // Validate required configuration
        if (empty($this->username) || empty($this->password)) {
            throw new Exception('Pandora SMS credentials are required. Set PANDORA_SMS_USERNAME and PANDORA_SMS_PASSWORD in .env');
        }

        $this->httpClient = new Client();
    }

    public function sendSms(string $number, string $message, string $sender, string $messageType, string $messageCategory): array
    {
        $params = [
            'query' => [
                'number' => urlencode($number),
                'message' => urlencode($message),
                'sender' => urlencode($sender),
                'username' => urlencode($this->username),
                'password' => urlencode($this->password),
                'message_type' => urlencode($messageType),
                'message_category' => urlencode($messageCategory)
            ]
        ];

        try {
            $response = $this->httpClient->get($this->baseUrl, $params);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            return ['success' => false, 'error_message' => $e->getMessage()];
        }
    }
}