<?php

namespace SquareetLabs\LaravelSmsUp;

use Illuminate\Support\Facades\Event;
use SquareetLabs\LaravelSmsUp\Events\SmsUpMessageWasSent;
use SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification;
use SquareetLabs\LaravelSmsUp\Exceptions\ValidationException;
use Illuminate\Notifications\Notification;

/**
 * Class SmsUpChannel
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpChannel
{
    /**
     * @var SmsUpManager
     */
    protected $smsUpManager;

    /**
     * SmsUpChannel constructor.
     *
     * @param SmsUpManager $smsUpManager
     */
    public function __construct(SmsUpManager $smsUpManager)
    {
        $this->smsUpManager = $smsUpManager;
    }

    /**
     * Enviar una notificación SMS
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @throws CouldNotSendNotification
     * @throws ValidationException
     */
    public function send($notifiable, Notification $notification)
    {
        // Obtener el mensaje de la notificación
        if (!method_exists($notification, 'toSmsUp')) {
            throw CouldNotSendNotification::configurationError(
                'Notification class must implement toSmsUp method'
            );
        }

        /** @var SmsUpMessage $message */
        $message = $notification->toSmsUp($notifiable);

        if (!$message instanceof SmsUpMessage) {
            throw CouldNotSendNotification::configurationError(
                'toSmsUp method must return an instance of SmsUpMessage'
            );
        }

        // Establecer destinatario si no está configurado
        if (empty($message->getTo())) {
            $to = $this->getRecipientPhoneNumber($notifiable);
            if (empty($to)) {
                throw CouldNotSendNotification::missingRecipient();
            }
            $message->to($to);
        }

        // Establecer remitente por defecto si no está configurado
        if (empty($message->getFrom()) && !empty(config('smsup.defaults.from'))) {
            $message->from(config('smsup.defaults.from'));
        }

        try {
            // Enviar el mensaje usando el manager
            $response = $this->smsUpManager->sendMessage($message);

            // Disparar evento de mensaje enviado
            Event::dispatch(new SmsUpMessageWasSent($message, $response));

            return $response;

        } catch (CouldNotSendNotification $e) {
            // Re-throw CouldNotSendNotification exceptions
            throw $e;
        } catch (ValidationException $e) {
            // Re-throw ValidationException exceptions
            throw $e;
        } catch (\Exception $e) {
            // Wrap other exceptions
            throw CouldNotSendNotification::serviceRespondedWithAnError(
                'Error inesperado al enviar SMS: ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Obtener el número de teléfono del destinatario
     *
     * @param mixed $notifiable
     * @return string|null
     */
    protected function getRecipientPhoneNumber($notifiable)
    {
        // Intentar múltiples formas de obtener el número de teléfono
        if ($phone = $notifiable->routeNotificationFor('smsUp')) {
            return $phone;
        }

        if ($phone = $notifiable->routeNotificationFor('smsup')) {
            return $phone;
        }

        if (isset($notifiable->phone)) {
            return $notifiable->phone;
        }

        if (isset($notifiable->mobile)) {
            return $notifiable->mobile;
        }

        if (isset($notifiable->phone_number)) {
            return $notifiable->phone_number;
        }

        return null;
    }
}
