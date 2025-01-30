<?php
namespace PandoraSMS;

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
        // Prioritize environment variables, fallback to constructor parameters
        $this->username = $username ?? env('PANDORA_SMS_USERNAME', '');
        $this->password = $password ?? env('PANDORA_SMS_PASSWORD', '');
        $this->baseUrl = $baseUrl ?? env('PANDORA_SMS_BASE_URL', 'https://www.sms.thepandoranetworks.com/API/send_sms/');

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
                'Number' => urlencode($number),
                'Message' => urlencode($message),
                'Sender' => urlencode($sender),
                'Username' => $this->username,
                'Password' => $this->password,
                'Message Type' => $messageType,
                'Message category' => $messageCategory
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
