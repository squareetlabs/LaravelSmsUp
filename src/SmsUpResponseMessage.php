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
     * @var string|null
     */
    private $smsId;

    /**
     * @var string|null
     */
    private $custom;

    /**
     * @var string|null
     */
    private $errorId;

    /**
     * @var string|null
     */
    private $errorMsg;

    /**
     * @var array
     */
    private $rawResponse;

    /**
     * SmsUpResponseMessage constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->rawResponse = $response;
        $this->status = $response['status'] ?? 'unknown';
        $this->smsId = $response['sms_id'] ?? null;
        $this->custom = $response['custom'] ?? null;
        $this->errorId = $response['error_id'] ?? null;
        $this->errorMsg = $response['error_msg'] ?? null;
    }

    /**
     * Obtiene el estado del mensaje
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Obtiene el ID del SMS asignado por SmsUp
     *
     * @return string|null
     */
    public function getSmsId()
    {
        return $this->smsId;
    }

    /**
     * Obtiene el identificador personalizado del mensaje
     *
     * @return string|null
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Obtiene el ID del error si existe
     *
     * @return string|null
     */
    public function getErrorId()
    {
        return $this->errorId;
    }

    /**
     * Obtiene el mensaje de error si existe
     *
     * @return string|null
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * Alias para getErrorMsg()
     *
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->getErrorMsg();
    }

    /**
     * Obtiene la respuesta cruda completa
     *
     * @return array
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * Verifica si el mensaje fue enviado exitosamente
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->status === 'ok';
    }

    /**
     * Verifica si hubo un error en el envío
     *
     * @return bool
     */
    public function hasError()
    {
        return !$this->isSuccessful();
    }

    /**
     * Verifica si el mensaje está pendiente
     *
     * @return bool
     */
    public function isPending()
    {
        return in_array($this->status, ['pending', 'queued', 'scheduled']);
    }

    /**
     * Verifica si el mensaje fue rechazado
     *
     * @return bool
     */
    public function isRejected()
    {
        return in_array($this->status, ['error', 'rejected', 'failed']);
    }

    /**
     * Obtiene información detallada del estado
     *
     * @return array
     */
    public function getStatusInfo()
    {
        return [
            'status' => $this->getStatus(),
            'successful' => $this->isSuccessful(),
            'pending' => $this->isPending(),
            'rejected' => $this->isRejected(),
            'has_error' => $this->hasError(),
            'sms_id' => $this->getSmsId(),
            'custom' => $this->getCustom(),
            'error_id' => $this->getErrorId(),
            'error_message' => $this->getErrorMessage(),
        ];
    }

    /**
     * Convierte el mensaje de respuesta a array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'status' => $this->status,
            'sms_id' => $this->smsId,
            'custom' => $this->custom,
            'error_id' => $this->errorId,
            'error_msg' => $this->errorMsg,
            'status_info' => $this->getStatusInfo(),
        ];
    }

    /**
     * Convierte el mensaje de respuesta a JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Representación en string del mensaje de respuesta
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->isSuccessful()) {
            $result = "SMS enviado exitosamente";
            if ($this->smsId) {
                $result .= " (ID: {$this->smsId})";
            }
            if ($this->custom) {
                $result .= " (Custom: {$this->custom})";
            }
            return $result;
        } else {
            $result = "Error al enviar SMS: {$this->getErrorMessage()}";
            if ($this->errorId) {
                $result .= " (Error ID: {$this->errorId})";
            }
            return $result;
        }
    }
}