<?php

namespace SquareetLabs\LaravelSmsUp;

use Illuminate\Support\Facades\Event;
use SquareetLabs\LaravelSmsUp\Events\SmsUpMessageWasSent;
use SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification;
use Illuminate\Notifications\Notification;
use SquareetLabs\LaravelSmsUp\Facades;

/**
 * Class SmsUpChannel
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpChannel
{
    /**
     * SmsUpChannel constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $notifiable
     * @param Notification $notification
     * @throws CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {
        /** @var SmsUpMessage $message */
        $message = $notification->toSmsUp($notifiable);

        if (empty($message->getTo())) {
            if (!$to = $notifiable->routeNotificationFor('smsUp')) {
                throw CouldNotSendNotification::missingRecipient();
            }
            $message->to($to);
        }

        $messages = [
            $message->formatData()
        ];
        $response = Facades\SmsUp::sendMessages($messages);

        $responseArray = [];
        array_push($responseArray, json_decode($response->getBody(), true));
        $reponseMessage = new SmsUpResponse($responseArray[0]);

        Event::dispatch(new SmsUpMessageWasSent($message, $reponseMessage));
    }
}
