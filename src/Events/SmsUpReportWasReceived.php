<?php

namespace SquareetLabs\LaravelSmsUp\Events;

use SquareetLabs\LaravelSmsUp\SmsUpReportResponse;

/**
 * Class SmsUpReportWasReceived
 * @package SquareetLabs\LaravelSmsUp\Events
 */
class SmsUpReportWasReceived
{
    /**
     * @var SmsUpReportResponse
     */
    public $response;

    /**
     * SmsUpReportWasReceived constructor.
     * @param SmsUpReportResponse $response
     */
    public function __construct(SmsUpReportResponse $response)
    {
        $this->response = $response;
    }
}