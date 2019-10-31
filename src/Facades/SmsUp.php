<?php

namespace SquareetLabs\LaravelSmsUp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class SmsUp
 * @package SquareetLabs\LaravelSmsUp\Facades
 */
class SmsUp extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'smsUp';
    }
}