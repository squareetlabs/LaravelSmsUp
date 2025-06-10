<?php

namespace SquareetLabs\LaravelSmsUp\Exceptions;

use Exception;
use Throwable;

/**
 * Class CouldNotSendNotification
 * @package SquareetLabs\LaravelSmsUp\Exceptions
 */
class CouldNotSendNotification extends Exception
{
    /**
     * Get a new could not send notification exception with
     * missing recipient message.
     *
     * @return static
     */
    public static function missingRecipient()
    {
        return new static('El destinatario del mensaje SMS no ha sido especificado.');
    }

    /**
     * Get a new could not send notification exception with
     * missing API key message.
     *
     * @return static
     */
    public static function missingApiKey()
    {
        return new static('La clave de API de SmsUp no ha sido configurada. Por favor, configura SMSUP_API_KEY en tu archivo .env');
    }

    /**
     * Get a new could not send notification exception with
     * missing message content.
     *
     * @return static
     */
    public static function missingMessage()
    {
        return new static('El contenido del mensaje SMS no puede estar vacío.');
    }

    /**
     * Get a new could not send notification exception with
     * invalid phone number format.
     *
     * @param string $phone
     * @return static
     */
    public static function invalidPhoneFormat($phone)
    {
        return new static("El formato del número de teléfono '{$phone}' no es válido. Debe incluir el código de país (ej: 34666666666).");
    }

    /**
     * Get a new could not send notification exception with
     * message too long.
     *
     * @param int $length
     * @param int $maxLength
     * @param string $encoding
     * @return static
     */
    public static function messageTooLong($length, $maxLength, $encoding = 'GSM7')
    {
        return new static("El mensaje es demasiado largo ({$length} caracteres). El máximo permitido para {$encoding} es {$maxLength} caracteres.");
    }

    /**
     * Get a new could not send notification exception with
     * invalid sender format.
     *
     * @param string $from
     * @return static
     */
    public static function invalidSenderFormat($from)
    {
        return new static("El formato del remitente '{$from}' no es válido. Máximo 15 caracteres numéricos o 11 alfanuméricos.");
    }

    /**
     * Get a new could not send notification exception with
     * API error response.
     *
     * @param string $message
     * @param int $code
     * @param array $response
     * @return static
     */
    public static function serviceRespondedWithAnError($message, $code = 0, $response = [])
    {
        $errorMessage = "Error del servicio SmsUp: {$message}";
        if (!empty($response)) {
            $errorMessage .= ' Respuesta: ' . json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        
        return new static($errorMessage, $code);
    }

    /**
     * Get a new could not send notification exception with
     * HTTP client error.
     *
     * @param Throwable $exception
     * @return static
     */
    public static function httpClientError(Throwable $exception)
    {
        return new static(
            "Error de comunicación con SmsUp: {$exception->getMessage()}",
            $exception->getCode(),
            $exception
        );
    }

    /**
     * Get a new could not send notification exception with
     * invalid encoding.
     *
     * @param string $encoding
     * @return static
     */
    public static function invalidEncoding($encoding)
    {
        return new static("Codificación '{$encoding}' no válida. Las opciones válidas son: GSM7, UCS2.");
    }

    /**
     * Get a new could not send notification exception with
     * configuration error.
     *
     * @param string $parameter
     * @return static
     */
    public static function configurationError($parameter)
    {
        return new static("Error de configuración: El parámetro '{$parameter}' es requerido.");
    }
}
