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
            if ($text = $message->getText()) {

                if ($text === '/start-anon') {
                    $bot->update([
                        'channel_id' => $message->getChat()->getId(),
                    ]);
                } elseif ($bot->channel_id) {
                    $api->sendMessage([
                        'chat_id' => $bot->channel_id,
                        'text' => $text
                    ]);
                }

            }
        }

        return 'ok';
    }
}
