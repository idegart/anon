<?php

Route::prefix('bot')->group(function () {
    Route::post('', 'Api\BotController@store');
    Route::delete('{token}', 'Api\BotController@destroy');

    Route::any('callback/{token}', 'Api\BotController@callback')->name('bot.callback');
});
