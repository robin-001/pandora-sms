<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Angstrom\PandoraSmsClient;

// Load environment variables from .env file if you have one
if (file_exists(__DIR__ . '/../.env')) {
    $envContent = file_get_contents(__DIR__ . '/../.env');
    foreach (explode("\n", $envContent) as $line) {
        if (trim($line) && !str_starts_with(trim($line), '#')) {
            putenv(trim($line));
        }
    }
}

// Create a new instance of PandoraSmsClient
try {
    $smsClient = new PandoraSmsClient();

    // Test message details
    $number = '0775065459'; // Local format
    $message = 'Hello! This is a test message from Pandora SMS Client.';
    $sender = 'Angstrom'; // Empty sender ID
    $messageType = 'non_customised'; // customised message type
    $messageCategory = 'bulk'; // bulk message category

    // Send the SMS
    echo "Sending SMS...\n";
    $result = $smsClient->sendSms($number, $message, $sender, $messageType, $messageCategory);

    // Display the result
    echo "\nResult:\n";
    echo "-------\n";
    echo json_encode($result, JSON_PRETTY_PRINT) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
