<?php

namespace SquareetLabs\LaravelSmsUp;

/**
 * Class SmsUpResponseMessage
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpResponseMessage
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $smsId;

    /**
     * @var string
     */
    private $custom;

    /**
     * @var string
     */
    private $errorId;

    /**
     * @var string
     */
    private $errorMsg;

    public function __construct(array $response)
    {
        $this->status = $response['status'];
        $this->smsId = $response['sms_id'];
        $this->custom = isset($response['custom']) ? $response['custom'] : '';
        $this->errorId = isset($response['error_id']) ? $response['error_id'] : '';
        $this->errorMsg = isset($response['error_msg']) ? $response['error_msg'] : '';
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getSmsId()
    {
        return $this->smsId;
    }

    /**
     * @return string|null
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @return string|null
     */
    public function getErrorId()
    {
        return $this->errorId;
    }

    /**
     * @return string|null
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }
}