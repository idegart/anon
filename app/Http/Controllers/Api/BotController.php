<?php

namespace App\Http\Controllers\Api;

use App\Bot;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Bot\DestroyRequest;
use App\Http\Requests\Api\Bot\StoreRequest;
use Telegram\Bot\Api;

class BotController extends Controller
{
    public function store(StoreRequest $request)
    {
        $api = new Api($request->getToken());

        $bot = Bot::withTrashed()->where('token', '=', $request->getToken())->first();

        if ($bot && $bot->trashed()) {
            $bot->restore();
        }

        if (!$bot) {
            $bot = Bot::query()->firstOrCreate([
                'bot_id' => $api->getMe()->getId(),
                'token' => $request->getToken(),
            ]);
        }

        $api->setWebhook([
            'url' => config('app.url') . '/api/bot/callback/' . $bot->token
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroy(DestroyRequest $request, Bot $bot)
    {
        $api = new Api($bot->token);

        $api->removeWebhook();

        $bot->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function callback(Bot $bot)
    {
        $api = new Api($bot->token);

        $updates = $api->getWebhookUpdates();

        if ($message = $updates->getMessage()) {
            if ($message->getMigrateFromChatId() || $message->getMigrateToChatId()) {
                $bot->update([
                    'channel_id' => $message->getChat()->getId(),
                ]);
            }

            if ($sticker = $message->getSticker()) {
                $api->sendSticker([
                    'chat_id' => $bot->channel_id,
                    'sticker' => $sticker->getFileId(),
                ]);
            }

            if ($photos = $message->getPhoto()) {
                $photo = $photos[0];
                $api->sendPhoto([
                    'chat_id' => $bot->channel_id,
                    'photo' => $photo['file_id']
                ]);
            }

            if ($document = $message->getDocument()) {
                $api->sendDocument([
                    'chat_id' => $bot->channel_id,
                    'document' => $document->getFileId(),
                ]);
            }

            if (($text = $message->getText()) || ($text = $message->getCaption())) {

                if ($message->getReplyToMessage()) {
                    return response('ok');
                }

                if ($text == '/start') {
                    $api->sendMessage([
                        'chat_id' => $message->getChat()->getId(),
                        'text' => 'Напишите мне анонимное сообщени и я отправлю его в общий чат от своего имени',
                    ]);
                } elseif ($text === '/start-anon') {
                    $bot->update([
                        'channel_id' => $message->getChat()->getId(),
                    ]);
                    $api->sendMessage([
                        'chat_id' => $bot->channel_id,
                        'text' => 'Бот активирован',
                    ]);
                } elseif ($bot->channel_id) {
                    $api->sendMessage([
                        'chat_id' => $bot->channel_id,
                        'text' => $text,
                    ]);
                    $api->sendMessage([
                        'chat_id' => $message->getChat()->getId(),
                        'text' => 'Ваше сообщение было анонимно отправлено'
                    ]);
                }
            }
        }

        return response('ok');
    }
}
