# Laravel SmsUp - Guía de Uso

Este paquete proporciona integración con la API de SmsUp (Gateway360) para Laravel 5.5 a 12.x.

## Instalación

```bash
composer require squareetlabs/laravel-smsup
```

### Publicar Configuración

```bash
php artisan vendor:publish --tag=smsup-config
```

### Configuración del .env

```env
SMSUP_API_KEY=tu_clave_api_aqui
SMSUP_DEFAULT_FROM=TuEmpresa
SMSUP_TEST_MODE=false
SMSUP_ENCODING=GSM7
```

## Uso Básico

### 1. Envío Simple de SMS

```php
use SquareetLabs\LaravelSmsUp\SmsUpMessage;
use SquareetLabs\LaravelSmsUp\Facades\SmsUp;

// Crear mensaje
$message = SmsUpMessage::create('34666666666', 'Hola, este es un mensaje de prueba', 'MiEmpresa');

// Enviar
$response = SmsUp::sendMessage($message);

if ($response->isSuccessful()) {
    echo "SMS enviado correctamente";
} else {
    echo "Error: " . $response->getErrorMessage();
}
```

### 2. Envío con Configuración Avanzada

```php
$message = new SmsUpMessage();
$message->to('34666666666')
        ->from('MiEmpresa')
        ->text('Mensaje con configuración avanzada')
        ->unicode() // Usar codificación UCS2 para emojis
        ->sendInMinutes(30) // Enviar en 30 minutos
        ->custom('pedido-123'); // Identificador personalizado

$response = SmsUp::sendMessage($message);
```

### 3. Envío Múltiple

```php
$messages = [
    SmsUpMessage::create('34666666666', 'Mensaje 1'),
    SmsUpMessage::create('34777777777', 'Mensaje 2'),
    SmsUpMessage::create('34888888888', 'Mensaje 3'),
];

$response = SmsUp::sendMessages($messages);

echo "Enviados: " . $response->getSuccessfulMessageCount() . "/" . $response->getMessageCount();
```

### 4. SMS con Enlaces

```php
$message = new SmsUpMessage();
$message->to('34666666666')
        ->from('MiEmpresa')
        ->text('Visita nuestro sitio web')
        ->link('https://www.miempresa.com');

$response = SmsUp::sendMessage($message);
```

## Uso con Notificaciones de Laravel

### 1. Crear Notificación

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use SquareetLabs\LaravelSmsUp\SmsUpMessage;

class PedidoConfirmado extends Notification
{
    private $pedido;

    public function __construct($pedido)
    {
        $this->pedido = $pedido;
    }

    public function via($notifiable)
    {
        return ['smsup'];
    }

    public function toSmsUp($notifiable)
    {
        return SmsUpMessage::create(
            $notifiable->phone,
            "Tu pedido #{$this->pedido->id} ha sido confirmado. Total: {$this->pedido->total}€",
            'MiTienda'
        );
    }
}
```

### 2. Configurar Modelo de Usuario

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Método para obtener el número de teléfono para SMS
    public function routeNotificationForSmsup()
    {
        return $this->phone; // o $this->mobile, etc.
    }
}
```

### 3. Enviar Notificación

```php
$user = User::find(1);
$pedido = Pedido::find(123);

$user->notify(new PedidoConfirmado($pedido));
```

## Características Avanzadas

### 1. Validación de Números

```php
try {
    $message = new SmsUpMessage();
    $message->to('numero_invalido'); // Lanzará excepción
} catch (\SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification $e) {
    echo "Error: " . $e->getMessage();
}
```

### 2. Verificación de Números

```php
$isValid = SmsUp::verifyPhone('34666666666');
if ($isValid) {
    echo "Número válido";
} else {
    echo "Número inválido";
}
```

### 3. Consultar Balance

```php
$balance = SmsUp::getBalance();
echo "Balance actual: " . $balance;
```

### 4. Programar Envíos

```php
use Carbon\Carbon;

$message = new SmsUpMessage();
$message->to('34666666666')
        ->text('Recordatorio de cita')
        ->sendAt(Carbon::tomorrow()->setTime(9, 0)); // Mañana a las 9:00

// O usar métodos de conveniencia
$message->sendInHours(2); // En 2 horas
$message->sendInMinutes(30); // En 30 minutos
```

### 5. Manejo de Respuestas

```php
$response = SmsUp::sendMessage($message);

// Información general
echo "Estado: " . $response->getStatus();
echo "Exitoso: " . ($response->isSuccessful() ? 'Sí' : 'No');
echo "Total mensajes: " . $response->getMessageCount();
echo "Exitosos: " . $response->getSuccessfulMessageCount();
echo "Fallidos: " . $response->getFailedMessageCount();

// Mensajes individuales
foreach ($response->getResult() as $messageResponse) {
    echo "SMS ID: " . $messageResponse->getSmsId();
    echo "Estado: " . $messageResponse->getStatus();
    if ($messageResponse->hasError()) {
        echo "Error: " . $messageResponse->getErrorMessage();
    }
}

// Resumen
print_r($response->getSummary());
```

### 6. Eventos

Escuchar cuando se envían mensajes:

```php
// En EventServiceProvider
protected $listen = [
    \SquareetLabs\LaravelSmsUp\Events\SmsUpMessageWasSent::class => [
        \App\Listeners\LogSmsUpMessage::class,
    ],
];
```

```php
// App\Listeners\LogSmsUpMessage
public function handle(\SquareetLabs\LaravelSmsUp\Events\SmsUpMessageWasSent $event)
{
    \Log::info('SMS enviado', [
        'to' => $event->message->getTo(),
        'status' => $event->response->getStatus(),
    ]);
}
```

## Configuración Avanzada

### Archivo config/smsup.php

```php
return [
    'api_key' => env('SMSUP_API_KEY'),
    'test_mode' => env('SMSUP_TEST_MODE', false),
    
    'defaults' => [
        'from' => env('SMSUP_DEFAULT_FROM', ''),
        'encoding' => env('SMSUP_ENCODING', 'GSM7'),
        'concat' => env('SMSUP_CONCAT', true),
    ],
    
    'validation' => [
        'validate_phone_format' => true,
        'max_message_length' => [
            'GSM7' => 459, // Para mensajes concatenados
            'UCS2' => 201, // Para mensajes Unicode concatenados
        ],
    ],
    
    'logging' => [
        'enabled' => true,
        'channel' => 'default',
        'level' => 'info',
    ],
];
```

## Manejo de Errores

```php
use SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification;
use SquareetLabs\LaravelSmsUp\Exceptions\ValidationException;

try {
    $response = SmsUp::sendMessage($message);
} catch (ValidationException $e) {
    // Errores de validación (formato de teléfono, mensaje muy largo, etc.)
    echo "Errores de validación:";
    foreach ($e->getErrors() as $error) {
        echo "- " . $error;
    }
} catch (CouldNotSendNotification $e) {
    // Errores de configuración o API
    echo "Error al enviar: " . $e->getMessage();
} catch (\Exception $e) {
    // Otros errores
    echo "Error inesperado: " . $e->getMessage();
}
```

## Codificaciones

### GSM7 (Por defecto)
- 160 caracteres por SMS
- 153 caracteres por SMS en mensajes concatenados
- Solo caracteres básicos (sin emojis ni acentos especiales)

### UCS2 (Unicode)
- 70 caracteres por SMS
- 67 caracteres por SMS en mensajes concatenados
- Soporte completo para emojis, acentos y caracteres especiales

```php
// Usar GSM7
$message->gsm7();

// Usar Unicode
$message->unicode();

// O especificar directamente
$message->encoding('UCS2');
```

## Webhooks (Reportes de Entrega)

El paquete incluye un endpoint para recibir reportes de entrega:

```
POST /smsup/report
```

Los reportes se procesan automáticamente y disparan el evento `SmsUpReportWasReceived`.

## Compatibilidad

- Laravel 5.5 a 12.x
- PHP 7.4 a 8.3
- Guzzle 6.2+ o 7.0+

## Soporte

Para soporte técnico, consulta la documentación oficial de SmsUp en:
https://api.gateway360.com/api/3.0/docs/sms/send 