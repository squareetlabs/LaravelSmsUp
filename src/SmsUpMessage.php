<?php

namespace SquareetLabs\LaravelSmsUp;

use Carbon\Carbon;

/**
 * Class SmsUpMessage
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpMessage
{
    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $link;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $sendAt;

    /**
     * @var string
     */
    private $custom;

    /**
     * @param string $to
     * @return SmsUpMessage
     */
    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $from
     * @return SmsUpMessage
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $link
     * @return SmsUpMessage
     */
    public function link($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $text
     * @return SmsUpMessage
     */
    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string|array|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $sendAt
     * @return SmsUpMessage
     */
    public function sendAt($sendAt)
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * @param string $custom
     * @return SmsUpMessage
     */
    public function custom($custom)
    {
        $this->custom = $custom;

        return $this;
    }

    /**
     * @return string|array|null
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @return array
     */
    public function formatData()
    {
        $payload = [];

        if (!empty($this->to)) {
            $payload['to'] = $this->to;
        }
        if (!empty($this->from)) {
            $payload['from'] = $this->from;
        }
        if (!empty($this->link)) {
            $payload['link'] = $this->link;
        }
        if (!empty($this->text)) {
            $payload['text'] = $this->text;
        }
        if (!empty($this->sendAt)) {
            $payload['send_at'] = $this->sendAt;
        } else {
            $payload['send_at'] = Carbon::now()->format('Y-m-d H:i:s');
        }
        if (!empty($this->custom)) {
            $payload['custom'] = $this->custom;
        }

        return $payload;
    }
}