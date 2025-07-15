# Laravel SmsUp - Integración API SmsUp/Gateway360

<p align="center">
<a href="https://scrutinizer-ci.com/g/squareetlabs/LaravelSmsUp/"><img src="https://scrutinizer-ci.com/g/squareetlabs/LaravelSmsUp/badges/quality-score.png?b=master" alt="Quality Score"></a>
<a href="https://scrutinizer-ci.com/g/squareetlabs/LaravelSmsUp/"><img src="https://scrutinizer-ci.com/g/squareetlabs/LaravelSmsUp/badges/code-intelligence.svg?b=master" alt="Code Intelligence"></a>
<a href="https://packagist.org/packages/squareetlabs/laravel-smsup"><img class="latest_stable_version_img" src="https://poser.pugx.org/squareetlabs/laravel-smsup/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/squareetlabs/laravel-smsup"><img class="total_img" src="https://poser.pugx.org/squareetlabs/laravel-smsup/downloads" alt="Total Downloads"></a> 
<a href="https://packagist.org/packages/squareetlabs/laravel-smsup"><img class="license_img" src="https://poser.pugx.org/squareetlabs/laravel-smsup/license" alt="License"></a>
</p>

Paquete Laravel para integración completa con la API de SmsUp (Gateway360). Compatible con Laravel 5.5 a 12.x.

## ✨ Características

- ✅ **Compatible con Laravel 5.5 - 12.x**
- ✅ **PHP 7.4 - 8.3**
- ✅ **Envío de SMS individuales y masivos**
- ✅ **Soporte para codificación GSM7 y UCS2 (Unicode)**
- ✅ **Programación de envíos**
- ✅ **SMS con enlaces**
- ✅ **Validación automática de números y mensajes**
- ✅ **Integración con Laravel Notifications**
- ✅ **Manejo avanzado de errores**
- ✅ **Logging configurable**
- ✅ **Eventos para tracking**
- ✅ **Webhooks para reportes de entrega**

## 📦 Instalación

```bash
composer require squareetlabs/laravel-smsup
```

### Publicar Configuración

```bash
php artisan vendor:publish --tag=smsup-config
```

### Configuración

Añade las siguientes variables a tu archivo `.env`:

```env
SMSUP_API_KEY=tu_clave_api_aqui
SMSUP_DEFAULT_FROM=TuEmpresa
SMSUP_TEST_MODE=false
SMSUP_ENCODING=GSM7
```

## 🚀 Uso Rápido

### Envío Simple

```php
use SquareetLabs\LaravelSmsUp\SmsUpMessage;
use SquareetLabs\LaravelSmsUp\Facades\SmsUp;

// Crear y enviar mensaje
$message = SmsUpMessage::create('34666666666', 'Hola mundo!', 'MiEmpresa');
$response = SmsUp::sendMessage($message);

if ($response->isSuccessful()) {
    echo "SMS enviado correctamente";
}
```

### Con Laravel Notifications

```php
// En tu notificación
public function via($notifiable)
{
    return ['smsup'];
}

public function toSmsUp($notifiable)
{
    return SmsUpMessage::create(
        $notifiable->phone,
        'Tu pedido ha sido confirmado',
        'MiTienda'
    );
}
```

## 📖 Documentación Completa

Para documentación detallada, ejemplos avanzados y todas las características, consulta [USAGE.md](USAGE.md).

## 🔧 Configuración Avanzada

El archivo de configuración `config/smsup.php` permite personalizar:

- **API y autenticación**
- **Valores por defecto** (remitente, codificación, etc.)
- **Validaciones** (formato de teléfonos, longitud de mensajes)
- **Logging** (canales, niveles)
- **Timeouts HTTP**
- **Reintentos automáticos**

## 🎯 Características Principales

### Codificaciones Soportadas

- **GSM7**: 160 caracteres por SMS (caracteres básicos)
- **UCS2**: 70 caracteres por SMS (Unicode completo, emojis)

```php
$message->gsm7();    // Para caracteres básicos
$message->unicode(); // Para emojis y caracteres especiales
```

### Programación de Envíos

```php
$message->sendInMinutes(30);           // En 30 minutos
$message->sendInHours(2);              // En 2 horas
$message->sendAt(Carbon::tomorrow());  // Fecha específica
```

### Validación Automática

El paquete valida automáticamente:
- Formato de números de teléfono
- Longitud de mensajes según codificación
- Formato de remitentes
- Campos requeridos

### Manejo de Respuestas

```php
$response = SmsUp::sendMessage($message);

echo "Total: " . $response->getMessageCount();
echo "Exitosos: " . $response->getSuccessfulMessageCount();
echo "Fallidos: " . $response->getFailedMessageCount();

// Detalles de cada mensaje
foreach ($response->getResult() as $messageResponse) {
    echo "SMS ID: " . $messageResponse->getSmsId();
    echo "Estado: " . $messageResponse->getStatus();
}
```

## 🔍 Funciones Adicionales

### Verificación de Números

```php
$isValid = SmsUp::verifyPhone('34666666666');
```

### Consulta de Balance

```php
$balance = SmsUp::getBalance();
```

### SMS con Enlaces

```php
$message->text('Visita nuestro sitio: {LINK}')
        ->link('https://www.miempresa.com');
```

## 📊 Eventos

El paquete dispara eventos para tracking:

- `SmsUpMessageWasSent`: Cuando se envía un mensaje
- `SmsUpReportWasReceived`: Cuando se recibe un reporte de entrega

```php
// En EventServiceProvider
protected $listen = [
    \SquareetLabs\LaravelSmsUp\Events\SmsUpMessageWasSent::class => [
        \App\Listeners\LogSmsMessage::class,
    ],
];
```

## 🛡️ Manejo de Errores

```php
use SquareetLabs\LaravelSmsUp\Exceptions\CouldNotSendNotification;
use SquareetLabs\LaravelSmsUp\Exceptions\ValidationException;

try {
    $response = SmsUp::sendMessage($message);
} catch (ValidationException $e) {
    // Errores de validación
    foreach ($e->getErrors() as $error) {
        echo "Error: " . $error;
    }
} catch (CouldNotSendNotification $e) {
    // Errores de API o configuración
    echo "Error: " . $e->getMessage();
}
```

## 🔗 Webhooks

El paquete incluye un endpoint automático para recibir reportes de entrega:

```
POST /smsup/report
```

Los reportes se procesan automáticamente y disparan eventos.

## 🧪 Modo de Prueba

Activa el modo de prueba para desarrollo:

```env
SMSUP_TEST_MODE=true
```

En modo de prueba, los SMS no se envían realmente pero se procesan normalmente.

## 📋 Requisitos

- Laravel 5.5 - 12.x
- PHP 7.4 - 8.3
- Guzzle HTTP 6.2+ o 7.0+
- Extensión JSON de PHP

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una rama para tu feature
3. Commit tus cambios
4. Push a la rama
5. Abre un Pull Request

## 📄 Licencia

Este paquete es open-source bajo la [Licencia MIT](LICENSE.md).

## 🆘 Soporte

- **Documentación completa**: [USAGE.md](USAGE.md)
- **API de SmsUp**: https://api.gateway360.com/api/3.0/docs/sms/send
- **Issues**: https://github.com/squareetlabs/LaravelSmsUp/issues

## 👥 Autores

- **Alberto Rial Barreiro** - [SquareetLabs](https://www.squareet.com)
- **Jacobo Cantorna Cigarrán** - [SquareetLabs](https://www.squareet.com)

---

⭐ Si este paquete te ha sido útil, ¡no olvides darle una estrella en GitHub!
