<?php

include_once('functions.php');

define('TOKEN', '6081035719:AAFldf70NidlYyoaUqKFkHN429lu1hvkYHY');
define('BASE_URL', 'https://api.telegram.org/bot' . TOKEN . '/');

$bot = new \TelegramBot\Api\Client(TOKEN);

$offset = 0;
$cityList = getCityList();
$companies = [];
$companyList = [];

while (true) {
    $updates = getUpdates($offset);

    foreach ($updates as $update) {
        $offset = $update->update_id + 1;
        $chatId = $update->message->chat->id;
        $text = $update->message->text;

        if ($text == '/start') {
            $bot->sendMessage($chatId, 'Hi! I am a food delivery bot. I can help you make an order :)');

            $keyboard = createCityKeyboard($cityList);
            $replyMarkup = new \TelegramBot\Api\Types\ReplyKeyboardMarkup($keyboard, false, true);
            $bot->sendMessage($chatId, 'Choose your city: ', null, false, null, $replyMarkup);
        } elseif (in_array($text, $cityList)) {
            $selectedCity = $text;
            $bot->sendMessage($chatId, "You have selected: $selectedCity");

            unset($companies);
            $companies = getCompanies($selectedCity);

            if (!empty($companies)) {
                // Display companies
                $bot->sendMessage($chatId, 'Available companies in this city:');
                foreach ($companies as $company) {
                    $bot->sendMessage($chatId, $company->name);
                }
            } else {
                $bot->sendMessage($chatId, 'No companies available in this city.');
            }
        } elseif (in_array($text, $companyList)) {
            $selectedCompany = $text;
            $bot->sendMessage($chatId, "You have selected company: $selectedCompany");
        }
    }

    sleep(1);
}
