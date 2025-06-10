<?php

namespace SquareetLabs\LaravelSmsUp;

use Carbon\Carbon;
use SquareetLabs\LaravelSmsUp\Exceptions\ValidationException;
use SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification;

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
     * @var string
     */
    private $encoding;

    /**
     * Establece el número de destino
     *
     * @param string $to Número de teléfono en formato internacional
     * @return SmsUpMessage
     * @throws CouldNotSendNotification
     */
    public function to($to)
    {
        if (!$this->isValidPhoneFormat($to)) {
            throw CouldNotSendNotification::invalidPhoneFormat($to);
        }

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
     * Establece el remitente del mensaje
     *
     * @param string $from Remitente (máximo 15 caracteres numéricos o 11 alfanuméricos)
     * @return SmsUpMessage
     * @throws CouldNotSendNotification
     */
    public function from($from)
    {
        if (!$this->isValidSenderFormat($from)) {
            throw CouldNotSendNotification::invalidSenderFormat($from);
        }

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
     * Establece un enlace para el mensaje (SMS con enlace)
     *
     * @param string $link URL del enlace
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
     * Establece el texto del mensaje
     *
     * @param string $text Contenido del mensaje
     * @return SmsUpMessage
     * @throws CouldNotSendNotification
     */
    public function text($text)
    {
        if (empty($text)) {
            throw CouldNotSendNotification::missingMessage();
        }

        $this->text = $text;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Establece cuándo enviar el mensaje
     *
     * @param string|Carbon $sendAt Fecha y hora de envío (YYYY-MM-DD HH:MM:SS)
     * @return SmsUpMessage
     */
    public function sendAt($sendAt)
    {
        if ($sendAt instanceof Carbon) {
            $this->sendAt = $sendAt->format('Y-m-d H:i:s');
        } else {
            $this->sendAt = $sendAt;
        }

        return $this;
    }

    /**
     * Programa el mensaje para enviarse ahora
     *
     * @return SmsUpMessage
     */
    public function sendNow()
    {
        $this->sendAt = Carbon::now()->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Programa el mensaje para enviarse en X minutos
     *
     * @param int $minutes
     * @return SmsUpMessage
     */
    public function sendInMinutes($minutes)
    {
        $this->sendAt = Carbon::now()->addMinutes($minutes)->format('Y-m-d H:i:s');
        return $this;
    }

    /**
     * Programa el mensaje para enviarse en X horas
     *
     * @param int $hours
     * @return SmsUpMessage
     */
    public function sendInHours($hours)
    {
        $this->sendAt = Carbon::now()->addHours($hours)->format('Y-m-d H:i:s');
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
     * Establece un identificador personalizado para el mensaje
     *
     * @param string $custom Identificador personalizado (máximo 32 caracteres alfanuméricos)
     * @return SmsUpMessage
     * @throws CouldNotSendNotification
     */
    public function custom($custom)
    {
        if (mb_strlen($custom) > 32) {
            throw CouldNotSendNotification::configurationError('custom field too long (max 32 characters)');
        }

        $this->custom = $custom;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Establece la codificación del mensaje
     *
     * @param string $encoding GSM7 o UCS2
     * @return SmsUpMessage
     * @throws CouldNotSendNotification
     */
    public function encoding($encoding)
    {
        $encoding = strtoupper($encoding);
        
        if (!in_array($encoding, [SmsUpManager::ENCODING_GSM7, SmsUpManager::ENCODING_UCS2])) {
            throw CouldNotSendNotification::invalidEncoding($encoding);
        }

        $this->encoding = $encoding;
        return $this;
    }

    /**
     * Establece la codificación como GSM7 (160 caracteres por SMS)
     *
     * @return SmsUpMessage
     */
    public function gsm7()
    {
        $this->encoding = SmsUpManager::ENCODING_GSM7;
        return $this;
    }

    /**
     * Establece la codificación como UCS2 (70 caracteres Unicode por SMS)
     *
     * @return SmsUpMessage
     */
    public function unicode()
    {
        $this->encoding = SmsUpManager::ENCODING_UCS2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Obtiene la longitud del mensaje en caracteres
     *
     * @return int
     */
    public function getLength()
    {
        return mb_strlen($this->text ?? '');
    }

    /**
     * Calcula cuántos SMS serán necesarios para enviar este mensaje
     *
     * @return int
     */
    public function getSmsCount()
    {
        $length = $this->getLength();
        $encoding = $this->encoding ?? SmsUpManager::ENCODING_GSM7;
        
        if ($encoding === SmsUpManager::ENCODING_UCS2) {
            // UCS2: 70 caracteres en un SMS, 67 en mensajes concatenados
            if ($length <= 70) {
                return 1;
            }
            return ceil($length / 67);
        } else {
            // GSM7: 160 caracteres en un SMS, 153 en mensajes concatenados
            if ($length <= 160) {
                return 1;
            }
            return ceil($length / 153);
        }
    }

    /**
     * Valida que el mensaje esté completo y sea válido
     *
     * @throws ValidationException
     */
    public function validate()
    {
        $errors = [];

        if (empty($this->to)) {
            $errors[] = 'Falta el número de destino';
        }

        if (empty($this->text)) {
            $errors[] = 'Falta el texto del mensaje';
        }

        if (empty($this->from)) {
            $errors[] = 'Falta el remitente del mensaje';
        }

        if (!empty($errors)) {
            throw ValidationException::withErrors($errors);
        }
    }

    /**
     * Convierte el mensaje a array para la API
     *
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

        if (!empty($this->encoding)) {
            $payload['encoding'] = $this->encoding;
        }

        return $payload;
    }

    /**
     * Crear un mensaje rápido con los parámetros básicos
     *
     * @param string $to
     * @param string $text
     * @param string|null $from
     * @return static
     */
    public static function create($to, $text, $from = null)
    {
        $message = new static();
        $message->to($to)->text($text);
        
        if ($from !== null) {
            $message->from($from);
        }

        return $message;
    }

    /**
     * Validar formato de teléfono
     *
     * @param string $phone
     * @return bool
     */
    protected function isValidPhoneFormat($phone)
    {
        return preg_match('/^\d{8,15}$/', $phone);
    }

    /**
     * Validar formato de remitente
     *
     * @param string $from
     * @return bool
     */
    protected function isValidSenderFormat($from)
    {
        if (is_numeric($from)) {
            return mb_strlen($from) <= 15;
        }
        
        return mb_strlen($from) <= 11 && preg_match('/^[a-zA-Z0-9\s]+$/', $from);
    }
}