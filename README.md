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
$client->sendSms('0700000000', 'Hello World!');
```