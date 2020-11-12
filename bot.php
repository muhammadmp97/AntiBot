<?php

use TeleBot\InlineKeyboard;
use TeleBot\TeleBot;
use Bot\Models\User;

require './vendor/autoload.php';

$tg = new TeleBot(BOT_TOKEN);

if ($tg->chat->id != CHAT_ID) {
    $tg->leaveChat(['chat_id' => $tg->chat->id]);
}

$user = $tg->getChatMember(['chat_id' => CHAT_ID, 'user_id' => $tg->user->id]);
$userIsAdmin = in_array($user->status, ['creator', 'administrator']);
$dbUser = User::firstWhere('tg_id', $tg->user->id);

try {
    if ($tg->message->new_chat_members && ! $dbUser) {
        $u = 0;
        foreach ($tg->message->new_chat_members as $newMember) {
            if ($u == 11) exit;
            $userFullName = '<a href="tg://user?id=' . $newMember->id . '">' . $newMember->first_name . ' ' . $newMember->last_name . '</a>';
            $tg->sendMessage([
                'chat_id' => CHAT_ID,
                'text' => "سلام {$userFullName}، لطفاً با فشردن دکمه‌ی زیر، ما را از اینکه ربات نیستید، مطمئن سازید.",
                'parse_mode' => 'html',
                'reply_markup' => (new InlineKeyboard(true))->addButton('من ربات نیستم!', null, null, $tg->user->id . '_' . 'notbot')->get()
            ]);
            $u++;
        }
    }

    $tg->listen('%d_notbot', function ($userId) use ($tg, $userIsAdmin) {
        if ($userId == $tg->user->id || $userIsAdmin) {
            User::create(['tg_id' => $userId]);

            $tg->answerCallbackQuery([
                'callback_query_id' => $tg->update->callback_query->id,
                'text' => 'ممنون! حدس می‌زدم! 😀'
            ]);

            $tg->deleteMessage(['chat_id' => CHAT_ID, 'message_id' => $tg->message->message_id]);
        } else {
            $tg->answerCallbackQuery([
                'callback_query_id' => $tg->update->callback_query->id,
                'text' => 'شما نمی‌توانید به جای کاربری دیگر پاسخ دهید!'
            ]);
        }
    });

    if (! $dbUser && ! $tg->update->callback_query && ! $userIsAdmin) {
        $tg->deleteMessage(['chat_id' => CHAT_ID, 'message_id' => $tg->message->message_id]);
        exit;
    }

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