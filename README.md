# Pandora SMS PHP Client

A PHP client for sending SMS via Pandora SMS API.

## Installation

```bash
composer require angstrom/pandora-sms
```

## Usage

```php 
use Angstrom\PandoraSms\PandoraSmsClient;

$client = new PandoraSmsClient();
// Send SMS
$result = $client->sendSms(
    '+1234567890', 
    'Hello, world!', 
    'YourSender', 
    'transactional', 
    'general'
);

echo $result;   
```
