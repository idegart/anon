<?php

namespace App;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Bot
 * @package App
 * @mixin Eloquent
 *
 * @property string $token
 * @property int $channel_id
 */
class Bot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'token', 'bot_id', 'channel_id',
    ];
}
