# Pandora SMS PHP Client

A PHP client for sending SMS via Pandora SMS API.

## Installation

```bash
composer require angstrom/pandora-sms
```

## Configuration

Create a `.env` file in your project root with your Pandora SMS credentials:

```env
PANDORA_SMS_USERNAME=your_username
PANDORA_SMS_PASSWORD=your_password
PANDORA_SMS_BASE_URL=https://www.sms.thepandoranetworks.com/API/send_sms/
```

## Usage

```php
use Angstrom\PandoraSmsClient;

// Initialize the client (it will use credentials from .env)
$client = new PandoraSmsClient();

// Send SMS
$result = $client->sendSms(
    '0712345678',           // Phone number
    'Hello, World!',        // Message
    'YourSenderID',         // Sender ID (must be approved)
    'non_customised',       // Message type (non_customised or customised)
    'bulk'                  // Message category (bulk)
);

// Handle the response
if ($result['success']) {
    echo "Message sent successfully!\n";
    echo "Balance: " . $result['data']['balance'] . "\n";
    echo "SMS Cost: " . $result['data']['sms_cost'] . "\n";
} else {
    echo "Error: " . ($result['messages'][0] ?? $result['error_message']) . "\n";
}
```

## Response Format

Successful response:
```json
{
    "statusCode": 201,
    "success": true,
    "messages": [
        "Message sent to 1 contacts. 0 were found to be unsupported. 0 contacts were duplicate and were merged."
    ],
    "data": {
        "supported_contacts": 1,
        "unsupported_contacts": 0,
        "sms_cost": 25,
        "balance": 9430
    }
}
```

## Parameters

- `number`: Phone number in local format (e.g., '0712345678')
- `message`: The SMS text content
- `sender`: Your approved sender ID
- `messageType`: Either 'non_customised' or 'customised'
- `messageCategory`: Use 'bulk' for sending messages
