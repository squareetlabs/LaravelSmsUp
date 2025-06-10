<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SmsUp API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración para la integración con SmsUp (Gateway360)
    | Documentación de la API: https://api.gateway360.com/api/3.0/docs/sms/send
    |
    */

    // Clave de API obtenida desde el panel de SmsUp
    'api_key' => env('SMSUP_API_KEY'),

    // URL base de la API
    'api_url' => env('SMSUP_API_URL', 'https://api.gateway360.com/api/'),

    // Modo de prueba (si está habilitado no se envían SMS reales)
    'test_mode' => env('SMSUP_TEST_MODE', false),

    // Configuración por defecto para los mensajes
    'defaults' => [
        // Remitente por defecto (hasta 15 caracteres numéricos o 11 alfanuméricos)
        'from' => env('SMSUP_DEFAULT_FROM', ''),
        
        // Habilitar concatenación de mensajes largos
        'concat' => env('SMSUP_CONCAT', true),
        
        // Codificación: 'GSM7' para caracteres básicos (160 caracteres) o 'UCS2' para Unicode (70 caracteres)
        'encoding' => env('SMSUP_ENCODING', 'GSM7'),
        
        // URL donde recibir los reportes de entrega (DLR)
        'report_url' => env('SMSUP_REPORT_URL'),
    ],

    // Configuración de timeouts para las peticiones HTTP
    'http' => [
        'timeout' => env('SMSUP_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('SMSUP_HTTP_CONNECT_TIMEOUT', 10),
    ],

    // Configuración de logs
    'logging' => [
        'enabled' => env('SMSUP_LOGGING', true),
        'channel' => env('SMSUP_LOG_CHANNEL', 'default'),
        'level' => env('SMSUP_LOG_LEVEL', 'info'),
    ],

    // Configuración de validaciones
    'validation' => [
        // Validar formato de números de teléfono
        'validate_phone_format' => env('SMSUP_VALIDATE_PHONE', true),
        
        // Longitud máxima del texto del mensaje
        'max_message_length' => [
            'GSM7' => 459, // Para mensajes concatenados
            'UCS2' => 201, // Para mensajes Unicode concatenados
        ],
        
        // Longitud máxima del campo custom
        'max_custom_length' => 32,
        
        // Longitud máxima del remitente
        'max_from_length' => [
            'numeric' => 15,
            'alphanumeric' => 11,
        ],
    ],

    // Configuración de reintentos
    'retry' => [
        'enabled' => env('SMSUP_RETRY_ENABLED', true),
        'max_attempts' => env('SMSUP_RETRY_MAX_ATTEMPTS', 3),
        'delay' => env('SMSUP_RETRY_DELAY', 1000), // En milisegundos
    ],
]; 