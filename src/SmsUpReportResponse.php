<?php

namespace SquareetLabs\LaravelSmsUp;

/**
 * Class SmsUpReportResponse
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpReportResponse
{
    /**
     * @var array
     */
    private $responseMessages;

    public function __construct(array $response)
    {
        foreach ($response as $responseMessage) {
            if (array_key_exists('status', $response)) {
                $this->responseMessages[] = new SmsUpReportResponseMessage($responseMessage);
            }
        }
    }

    /**
     * @return array
     */
    public function getResponseMessages()
    {
        return $this->responseMessages;
    }
}