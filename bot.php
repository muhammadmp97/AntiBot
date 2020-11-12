<?php
use TeleBot\TeleBot;

require './vendor/autoload.php';

$tg = new TeleBot(BOT_TOKEN);

if ($tg->chat->id != CHAT_ID) {
    $tg->leaveChat(['chat_id' => $tg->chat->id]);
}

try {
    $tg->listen('!ban', function () use ($tg) {
        $user = $tg->message->reply_to_message->from;
        $senderInfo = $tg->getChatMember(['chat_id' => CHAT_ID, 'user_id' => $tg->user->id]);

        if (in_array($senderInfo->status, ['creator', 'administrator'])) {
            $tg->kickChatMember(['chat_id' => CHAT_ID, 'user_id' => $user->id]);
        }
    });
} catch (Exception $e) {
    tl($e->getMessage());
}