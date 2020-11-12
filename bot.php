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

    $tg->listen('!unban', function () use ($tg) {
        $user = $tg->message->reply_to_message->from;
        $senderInfo = $tg->getChatMember(['chat_id' => CHAT_ID, 'user_id' => $tg->user->id]);

        if (in_array($senderInfo->status, ['creator', 'administrator'])) {
            $tg->unbanChatMember(['chat_id' => CHAT_ID, 'user_id' => $user->id]);
        }
    });

    $tg->listen('!mute', function () use ($tg) {
        $user = $tg->message->reply_to_message->from;
        $senderInfo = $tg->getChatMember(['chat_id' => CHAT_ID, 'user_id' => $tg->user->id]);

        if (in_array($senderInfo->status, ['creator', 'administrator'])) {
            $tg->restrictChatMember([
                'chat_id' => CHAT_ID,
                'user_id' => $user->id,
                'permissions' => '{}'
            ]);
        }
    });

    $tg->listen('!unmute', function () use ($tg) {
        $user = $tg->message->reply_to_message->from;
        $senderInfo = $tg->getChatMember(['chat_id' => CHAT_ID, 'user_id' => $tg->user->id]);

        if (in_array($senderInfo->status, ['creator', 'administrator'])) {
            $tg->restrictChatMember([
                'chat_id' => CHAT_ID,
                'user_id' => $user->id,
                'permissions' => '{"can_send_messages": true, "can_send_media_messages": true, "can_send_polls": true, "can_send_other_messages": true, "can_invite_users": true}'
            ]);
        }
    });
} catch (Exception $e) {
    tl($e->getMessage());
}