<?php

namespace SquareetLabs\LaravelSmsUp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Support\Facades\Log;
use SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification;
use SquareetLabs\LaravelSmsUp\Exceptions\ValidationException;

/**
 * Class SmsUpManager
 * @package SquareetLabs\LaravelSmsUp
 */
class SmsUpManager
{
    /**
     * @const The API URL for SmsUp
     */
    const API_URI = 'https://api.gateway360.com/api/';

    /**
     * @const The API endpoint to send messages
     */
    const ENDPOINT_SEND = '3.0/sms/send';

    /**
     * @const The API endpoint to send messages with link
     */
    const ENDPOINT_SEND_LINK = '3.0/sms/send-link';

    /**
     * @const The API endpoint to verify phone number
     */
    const ENDPOINT_VERIFY = 'hlr/request';

    /**
     * @const The API endpoint to get account balance
     */
    const ENDPOINT_BALANCE = '3.0/account/get-balance';

    /**
     * @const Supported encodings
     */
    const ENCODING_GSM7 = 'GSM7';
    const ENCODING_UCS2 = 'UCS2';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $config;

    /**
     * SmsUpManager constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->client = new Client([
            'base_uri' => $this->config['api_url'] ?? self::API_URI,
            'timeout' => $this->config['http']['timeout'] ?? 30,
            'connect_timeout' => $this->config['http']['connect_timeout'] ?? 10,
        ]);
    }

    /**
     * Enviar mensajes SMS
     *
     * @param array $messages
     * @param array $options Opciones adicionales (concat, encoding, report_url, fake)
     * @return SmsUpResponse
     * @throws CouldNotSendNotification
     * @throws ValidationException
     */
    public function sendMessages(array $messages, array $options = [])
    {
        // Validar configuración
        $this->validateConfiguration();

        // Validar y procesar mensajes
        $processedMessages = $this->validateAndProcessMessages($messages);

        // Preparar datos para la API
        $data = $this->prepareApiData($processedMessages, $options);

        // Determinar endpoint
        $endpoint = $this->hasLinks($processedMessages) ? self::ENDPOINT_SEND_LINK : self::ENDPOINT_SEND;

        try {
            // Realizar petición
            $response = $this->makeApiRequest($endpoint, $data);
            
            // Procesar respuesta
            $smsUpResponse = $this->processResponse($response);
            
            // Log successful send
            $this->logSuccess($processedMessages, $smsUpResponse);
            
            return $smsUpResponse;
            
        } catch (RequestException $e) {
            $this->logError('HTTP error', $e);
            throw CouldNotSendNotification::httpClientError($e);
        } catch (\Exception $e) {
            $this->logError('General error', $e);
            throw $e;
        }
    }

    /**
     * Enviar un mensaje SMS individual
     *
     * @param SmsUpMessage $message
     * @param array $options
     * @return SmsUpResponse
     * @throws CouldNotSendNotification
     * @throws ValidationException
     */
    public function sendMessage(SmsUpMessage $message, array $options = [])
    {
        return $this->sendMessages([$message->formatData()], $options);
    }

    /**
     * Verificar un número de teléfono
     *
     * @param string $phone
     * @return bool
     * @throws CouldNotSendNotification
     */
    public function verifyPhone($phone)
    {
        $this->validateConfiguration();

        if (!$this->isValidPhoneFormat($phone)) {
            throw CouldNotSendNotification::invalidPhoneFormat($phone);
        }

        $data = [
            'api_key' => $this->config['api_key'],
            'msisdn' => $phone
        ];

        try {
            $response = $this->makeApiRequest(self::ENDPOINT_VERIFY, $data);
            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['status']) && $responseData['status'] === 'ok') {
                return $responseData['result']['success'] ?? false;
            }

            return false;

        } catch (RequestException $e) {
            $this->logError('Phone verification error', $e);
            throw CouldNotSendNotification::httpClientError($e);
        }
    }

    /**
     * Obtener balance de la cuenta
     *
     * @return string|float
     * @throws CouldNotSendNotification
     */
    public function getBalance()
    {
        $this->validateConfiguration();

        $data = [
            'api_key' => $this->config['api_key']
        ];

        try {
            $response = $this->makeApiRequest(self::ENDPOINT_BALANCE, $data);
            $responseData = json_decode($response->getBody(), true);

            if (isset($responseData['status']) && $responseData['status'] === 'ok') {
                return $responseData['result']['balance'] ?? 'unknown';
            }

            return 'error';

        } catch (RequestException $e) {
            $this->logError('Balance retrieval error', $e);
            throw CouldNotSendNotification::httpClientError($e);
        }
    }

    /**
     * Validar configuración básica
     *
     * @throws CouldNotSendNotification
     */
    protected function validateConfiguration()
    {
        if (empty($this->config['api_key'])) {
            throw CouldNotSendNotification::missingApiKey();
        }
    }

    /**
     * Validar y procesar mensajes
     *
     * @param array $messages
     * @return array
     * @throws ValidationException
     */
    protected function validateAndProcessMessages(array $messages)
    {
        if (empty($messages)) {
            throw ValidationException::withErrors(['No se han proporcionado mensajes para enviar']);
        }

        $errors = [];
        $processedMessages = [];

        foreach ($messages as $index => $message) {
            try {
                $processedMessages[] = $this->validateAndProcessMessage($message, $index);
            } catch (ValidationException $e) {
                $errors = array_merge($errors, $e->getErrors());
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withErrors($errors);
        }

        return $processedMessages;
    }

    /**
     * Validar y procesar un mensaje individual
     *
     * @param array $message
     * @param int $index
     * @return array
     * @throws ValidationException
     */
    protected function validateAndProcessMessage(array $message, $index = 0)
    {
        $errors = [];

        // Validar destinatario
        if (empty($message['to'])) {
            $errors[] = "Mensaje #{$index}: falta el destinatario";
        } elseif ($this->config['validation']['validate_phone_format'] ?? true) {
            if (!$this->isValidPhoneFormat($message['to'])) {
                $errors[] = "Mensaje #{$index}: formato de teléfono inválido '{$message['to']}'";
            }
        }

        // Validar mensaje
        if (empty($message['text'])) {
            $errors[] = "Mensaje #{$index}: el texto del mensaje no puede estar vacío";
        } else {
            $encoding = $message['encoding'] ?? $this->config['defaults']['encoding'] ?? self::ENCODING_GSM7;
            $maxLength = $this->getMaxMessageLength($encoding);
            
            if (mb_strlen($message['text']) > $maxLength) {
                $errors[] = "Mensaje #{$index}: texto demasiado largo (" . mb_strlen($message['text']) . " caracteres, máximo {$maxLength} para {$encoding})";
            }
        }

        // Validar remitente
        if (!empty($message['from'])) {
            if (!$this->isValidSenderFormat($message['from'])) {
                $errors[] = "Mensaje #{$index}: formato de remitente inválido '{$message['from']}'";
            }
        } elseif (empty($this->config['defaults']['from'])) {
            $errors[] = "Mensaje #{$index}: falta el remitente y no hay remitente por defecto configurado";
        }

        // Validar custom field
        if (!empty($message['custom']) && mb_strlen($message['custom']) > ($this->config['validation']['max_custom_length'] ?? 32)) {
            $errors[] = "Mensaje #{$index}: campo 'custom' demasiado largo";
        }

        if (!empty($errors)) {
            throw ValidationException::withErrors($errors);
        }

        // Aplicar valores por defecto
        $message['from'] = $message['from'] ?? $this->config['defaults']['from'];
        $message['encoding'] = $message['encoding'] ?? $this->config['defaults']['encoding'] ?? self::ENCODING_GSM7;

        return $message;
    }

    /**
     * Preparar datos para la API
     *
     * @param array $messages
     * @param array $options
     * @return array
     */
    protected function prepareApiData(array $messages, array $options = [])
    {
        $data = [
            'api_key' => $this->config['api_key'],
            'messages' => $messages,
            'concat' => $options['concat'] ?? $this->config['defaults']['concat'] ?? 1,
            'fake' => $options['fake'] ?? ($this->config['test_mode'] ? 1 : 0),
        ];

        // Añadir encoding si es UCS2
        $encoding = $options['encoding'] ?? $this->config['defaults']['encoding'] ?? self::ENCODING_GSM7;
        if ($encoding === self::ENCODING_UCS2) {
            $data['encoding'] = 'UCS2';
        }

        // Añadir report_url si está configurado
        $reportUrl = $options['report_url'] ?? $this->config['defaults']['report_url'];
        if (!empty($reportUrl)) {
            $data['report_url'] = $reportUrl;
        } elseif ($this->hasRoute('smsup.report')) {
            $data['report_url'] = route('smsup.report');
        }

        // Si hay links, procesarlos
        if ($this->hasLinks($messages)) {
            $data['link'] = $messages[0]['link'];
        }

        return $data;
    }

    /**
     * Realizar petición a la API
     *
     * @param string $endpoint
     * @param array $data
     * @return ResponseInterface
     * @throws RequestException
     */
    protected function makeApiRequest($endpoint, array $data)
    {
        return $this->client->post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $data,
        ]);
    }

    /**
     * Procesar respuesta de la API
     *
     * @param ResponseInterface $response
     * @return SmsUpResponse
     * @throws CouldNotSendNotification
     */
    protected function processResponse(ResponseInterface $response)
    {
        $responseData = json_decode($response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw CouldNotSendNotification::serviceRespondedWithAnError('Respuesta JSON inválida');
        }

        $smsUpResponse = new SmsUpResponse($responseData);

        // Verificar si hay errores en la respuesta
        if (!$smsUpResponse->isSuccessful()) {
            throw CouldNotSendNotification::serviceRespondedWithAnError(
                $smsUpResponse->getErrorMessage() ?? 'Error desconocido',
                $response->getStatusCode(),
                $responseData
            );
        }

        return $smsUpResponse;
    }

    /**
     * Verificar si los mensajes contienen links
     *
     * @param array $messages
     * @return bool
     */
    protected function hasLinks(array $messages)
    {
        foreach ($messages as $message) {
            if (!empty($message['link'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Validar formato de teléfono
     *
     * @param string $phone
     * @return bool
     */
    protected function isValidPhoneFormat($phone)
    {
        // Básica validación: debe ser numérico y tener código de país
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
        $config = $this->config['validation']['max_from_length'] ?? ['numeric' => 15, 'alphanumeric' => 11];
        
        if (is_numeric($from)) {
            return mb_strlen($from) <= $config['numeric'];
        }
        
        return mb_strlen($from) <= $config['alphanumeric'] && preg_match('/^[a-zA-Z0-9\s]+$/', $from);
    }

    /**
     * Obtener longitud máxima del mensaje según la codificación
     *
     * @param string $encoding
     * @return int
     */
    protected function getMaxMessageLength($encoding)
    {
        $maxLengths = $this->config['validation']['max_message_length'] ?? [
            'GSM7' => 459,
            'UCS2' => 201,
        ];

        return $maxLengths[$encoding] ?? $maxLengths['GSM7'];
    }

    /**
     * Verificar si una ruta existe
     *
     * @param string $routeName
     * @return bool
     */
    protected function hasRoute($routeName)
    {
        try {
            if (function_exists('route')) {
                route($routeName);
                return true;
            }
        } catch (\Exception $e) {
            // Ruta no existe
        }
        
        return false;
    }

    /**
     * Log successful operation
     *
     * @param array $messages
     * @param SmsUpResponse $response
     */
    protected function logSuccess(array $messages, SmsUpResponse $response)
    {
        if (!($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $channel = $this->config['logging']['channel'] ?? 'default';
        $level = $this->config['logging']['level'] ?? 'info';

        Log::channel($channel)->{$level}('SmsUp: Mensajes enviados exitosamente', [
            'message_count' => count($messages),
            'response_status' => $response->getStatus(),
            'response_result' => $response->getResult(),
        ]);
    }

    /**
     * Log error
     *
     * @param string $message
     * @param \Exception $exception
     */
    protected function logError($message, \Exception $exception)
    {
        if (!($this->config['logging']['enabled'] ?? true)) {
            return;
        }

        $channel = $this->config['logging']['channel'] ?? 'default';

        Log::channel($channel)->error("SmsUp: {$message}", [
            'exception' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);
    }
}