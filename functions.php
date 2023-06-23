<?php

define('conn', "'localhost', 'root', 'd@tab@seadmin257', 'food_delivery'");

require 'vendor/autoload.php';

use GuzzleHttp\Client;

function getUpdates($offset)
{
    $url = 'https://api.telegram.org/bot' . TOKEN . '/getUpdates?offset=' . $offset;
    $response = file_get_contents($url);
    $data = json_decode($response);

    if (isset($data->result)) {
        return $data->result;
    }

    return [];
}

function sendMessage($params = [])
{
    $url = 'https://api.telegram.org/bot' . TOKEN . '/sendMessage?' . http_build_query($params);
    file_get_contents($url);
}

function getCityList()
{

    $conn = new mysqli('localhost', 'root', 'd@tab@seadmin257', 'food_delivery');

    $sql = "SELECT * FROM cities";
    $result = $conn->query($sql);

    $cities = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cities[] = $row['name'];
        }
    }

    return $cities;
}


function saveCitiesToDatabase($cities)
{
    $conn = new mysqli('localhost', 'root', 'd@tab@seadmin257', 'food_delivery');

    foreach ($cities as $city) {
        $id = $city->id;
        $name = $city->name;

        $sql = "SELECT id FROM cities WHERE apiId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            continue;
        }

        $sql = "INSERT INTO cities (apiId, name) VALUES (?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $id, $name);
        $stmt->execute();
    }
}

function createCityKeyboard($cityList)
{
    $keyboard = [];
    foreach ($cityList as $city) {
        $keyboard[] = [$city];
    }
    return $keyboard;
}

function saveCompaniesToDatabase($companies)
{
    $conn = new mysqli('localhost', 'root', 'd@tab@seadmin257', 'food_delivery');

    if ($conn->connect_error) {
        die("Помилка підключення до бази даних: " . $conn->connect_error);
    }

    $insertedIds = [];


    foreach ($companies as $company) {
        $id = $company->id;
        $name = $company->name;

        $sql = "SELECT id FROM companies WHERE companyId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            continue;
        }

        $sql = "INSERT INTO companies (companyId, companyName) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $id, $name);
        $stmt->execute();

        $insertedIds[] = $stmt->insert_id;
    }

    return $insertedIds;

    $stmt->close();
    $conn->close();
}


function getCompanies($selectedCity)
{
    $pdo = new PDO("mysql:host=localhost;dbname=food_delivery;charset=utf8", 'root', 'd@tab@seadmin257');

    $query = $pdo->prepare("SELECT apiId FROM cities WHERE name = :name");
    $query->execute(['name' => $selectedCity]);

    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $cityId = $result['apiId']; 

        $client = new \GuzzleHttp\Client();
        $response = $client->get(
            'https://clients-api.dots.live/api/v2/cities/' . $cityId . '/companies',
            [
                'headers' => [
                    'Api-Token' => 'Yz2zKOAozjl2n4KaTKw6fyfBEdMf7Wpi',
                    'Api-Account-Token' => '8f188b90-600b-11e9-974b-4337805ceaca',
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'v' => '2.0.0',
                ],
            ]
        );

        $body = $response->getBody();
        $data = json_decode((string) $body);

        if (isset($data->companies) && is_array($data->companies) && count($data->companies) > 0) {
            return $data->companies;
        }
    }

    return [];
}


function createCompanyKeyboard($companies)
{
    $keyboard = [];
    foreach ($companies as $company) {
        $keyboard[] = [$company->name];
    }
    $keyboard[] = ['Back']; 
    
    print_r($keyboard);
    
    return $keyboard;
}

?>


