<?php

namespace SquareetLabs\LaravelSmsUp;

/**
 * Class SmsUpReportResponseMessage
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpReportResponseMessage
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
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $smsDate;

    /**
     * @var string
     */
    private $dlrDate;

    public function __construct(array $response)
    {
        $this->status = $response['status'];
        $this->smsId = $response['sms_id'];
        $this->from = $response['from'];
        $this->to = $response['to'];
        $this->custom = isset($response['custom']) ? $response['custom'] : '';
        $this->smsDate = isset($response['sms_date']) ? $response['sms_date'] : '';
        $this->dlrDate = isset($response['dlr_date']) ? $response['dlr_date'] : '';
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
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return string|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @return string|null
     */
    public function getSmsDate()
    {
        return $this->smsDate;
    }

    /**
     * @return string|null
     */
    public function getDlrDate()
    {
        return $this->dlrDate;
    }
}