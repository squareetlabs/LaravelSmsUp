<?php

namespace SquareetLabs\LaravelSmsUp\Exceptions;

/**
 * Class CouldNotSendNotification
 * @package SquareetLabs\LaravelSmsUp\Exceptions
 */
class CouldNotSendNotification extends \Exception
{
    /**
     * Get a new could not send notification exception with
     * missing recipient message.
     *
     * @return static
     */
    public static function missingRecipient()
    {
        $message = 'The recipient of the sms message is missing.';
        return new static($message);
    }
}