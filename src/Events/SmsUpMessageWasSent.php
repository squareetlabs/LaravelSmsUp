<?php

namespace SquareetLabs\LaravelSmsUp\Events;

use SquareetLabs\LaravelSmsUp\SmsUpMessage;
use SquareetLabs\LaravelSmsUp\SmsUpResponse;

/**
 * Class SmsUpMessageWasSent
 * @package SquareetLabs\LaravelSmsUp\Events
 */
class SmsUpMessageWasSent
{
    /**
     * @var SmsUpMessage
     */
    public $message;

    /**
     * @var SmsUpResponse
     */
    public $response;

    /**
     * SmsUpMessageWasSent constructor.
     * @param SmsUpMessage $message
     * @param SmsUpResponse $response
     */
    public function __construct(SmsUpMessage $message, SmsUpResponse $response)
    {
        $this->message = $message;
        $this->response = $response;
    }
}