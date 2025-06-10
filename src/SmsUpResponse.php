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
     * SmsUpResponse constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->rawResponse = $response;
        $this->status = $response['status'] ?? 'unknown';
        $this->errorId = $response['error_id'] ?? null;
        $this->errorMsg = $response['error_msg'] ?? null;
        
        $this->result = [];
        
        if (isset($response['result']) && is_array($response['result'])) {
            foreach ($response['result'] as $responseMessage) {
                if (is_array($responseMessage)) {
                    $this->result[] = new SmsUpResponseMessage($responseMessage);
                }
            }
        }
    }

    /**
     * Obtiene el estado de la respuesta
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Obtiene los resultados de los mensajes enviados
     *
     * @return array|SmsUpResponseMessage[]
     */
    public function getResult()
    {
        return $this->result;
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
     * Verifica si la respuesta fue exitosa
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->status === 'ok';
    }

    /**
     * Verifica si hubo un error
     *
     * @return bool
     */
    public function hasError()
    {
        return !$this->isSuccessful();
    }

    /**
     * Obtiene el número total de mensajes procesados
     *
     * @return int
     */
    public function getMessageCount()
    {
        return count($this->result);
    }

    /**
     * Obtiene el número de mensajes enviados exitosamente
     *
     * @return int
     */
    public function getSuccessfulMessageCount()
    {
        $count = 0;
        foreach ($this->result as $message) {
            if ($message->isSuccessful()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Obtiene el número de mensajes que fallaron
     *
     * @return int
     */
    public function getFailedMessageCount()
    {
        return $this->getMessageCount() - $this->getSuccessfulMessageCount();
    }

    /**
     * Verifica si todos los mensajes fueron enviados exitosamente
     *
     * @return bool
     */
    public function allMessagesSuccessful()
    {
        if (!$this->isSuccessful()) {
            return false;
        }

        foreach ($this->result as $message) {
            if (!$message->isSuccessful()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtiene los mensajes que fallaron
     *
     * @return array|SmsUpResponseMessage[]
     */
    public function getFailedMessages()
    {
        $failed = [];
        foreach ($this->result as $message) {
            if (!$message->isSuccessful()) {
                $failed[] = $message;
            }
        }
        return $failed;
    }

    /**
     * Obtiene los mensajes exitosos
     *
     * @return array|SmsUpResponseMessage[]
     */
    public function getSuccessfulMessages()
    {
        $successful = [];
        foreach ($this->result as $message) {
            if ($message->isSuccessful()) {
                $successful[] = $message;
            }
        }
        return $successful;
    }

    /**
     * Obtiene un resumen de la respuesta
     *
     * @return array
     */
    public function getSummary()
    {
        return [
            'status' => $this->getStatus(),
            'successful' => $this->isSuccessful(),
            'total_messages' => $this->getMessageCount(),
            'successful_messages' => $this->getSuccessfulMessageCount(),
            'failed_messages' => $this->getFailedMessageCount(),
            'error_id' => $this->getErrorId(),
            'error_message' => $this->getErrorMessage(),
        ];
    }

    /**
     * Convierte la respuesta a array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'status' => $this->status,
            'result' => array_map(function ($message) {
                return $message->toArray();
            }, $this->result),
            'error_id' => $this->errorId,
            'error_msg' => $this->errorMsg,
            'summary' => $this->getSummary(),
        ];
    }

    /**
     * Convierte la respuesta a JSON
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Representación en string de la respuesta
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->isSuccessful()) {
            return "SmsUp Response: {$this->getSuccessfulMessageCount()}/{$this->getMessageCount()} mensajes enviados exitosamente";
        } else {
            return "SmsUp Response Error: {$this->getErrorMessage()} (ID: {$this->getErrorId()})";
        }
    }
}