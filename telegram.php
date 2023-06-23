<?php

require_once('functions.php');

use Telegram\Bot\Api;
use Telegram\Bot\Keyboard\Keyboard;

$response = $client->get(
    'https://clients-api.dots.live/api/v2/cities', 
    [
        'headers' => [
            'Api-Token' => 'Yz2zKOAozjl2n4KaTKw6fyfBEdMf7Wpi',
            'Api-Account-Token' => '8f188b90-600b-11e9-974b-4337805ceaca',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
        'query' => [
            'v'=> '2.0.0',
        ],
    ]
);
$body = $response->getBody();
$data = json_decode($body);
print_r($data);

if (!empty($data->items)) {
    
    $conn = new mysqli('localhost', 'root', 'd@tab@seadmin257', 'food_delivery');

    
    if ($conn->connect_error) {
        die('DB connection error: ' . $conn->connect_error);
    }

    saveCitiesToDatabase($data->items);

        $stmt->execute();

    $stmt->close();
    $conn->close();
}

?>