<?php
use TeleBot\TeleBot;

require './vendor/autoload.php';

$tg = new TeleBot(BOT_TOKEN);

if ($tg->chat->id != CHAT_ID) {
    $tg->leaveChat(['chat_id' => $tg->chat->id]);
}