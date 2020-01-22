<?php

namespace SquareetLabs\LaravelSmsUp;

/**
 * Class SmsUpResponse
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpResponse
{
    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $result;

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
        $this->errorId = isset($response['error_id']) ? $response['error_id'] : '';
        $this->errorMsg = isset($response['error_msg']) ? $response['error_msg'] : '';
        foreach ($response['result'] as $responseMessage) {
            if (array_key_exists('status', $response)) {
                $this->result[] = new SmsUpResponseMessage($responseMessage);
            }
        }
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
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