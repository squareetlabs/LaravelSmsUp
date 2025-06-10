# Changelog

## [2.0.0] - 2025-06-10

### 🚀 Nuevas Características

#### Compatibilidad Extendida
- ✅ **Soporte para Laravel 12.x** - Compatibilidad completa con Laravel 5.5 a 12.x
- ✅ **PHP 8.3** - Soporte para PHP 7.4 a 8.3
- ✅ **Guzzle 7.x** - Compatibilidad con Guzzle 6.2+ y 7.x

#### API y Funcionalidades
- ✅ **Codificación UCS2/Unicode** - Soporte completo para emojis y caracteres especiales
- ✅ **Programación de envíos** - Métodos para programar SMS en el futuro
- ✅ **Validación avanzada** - Validación automática de números, mensajes y configuración
- ✅ **Método sendMessage()** - Nuevo método para envío de mensajes individuales
- ✅ **Método create()** - Factory method para creación rápida de mensajes

#### Configuración y Gestión
- ✅ **Archivo de configuración** - Nuevo archivo `config/smsup.php` con opciones avanzadas
- ✅ **Variables de entorno** - Configuración completa vía `.env`
- ✅ **Logging configurable** - Sistema de logs personalizable
- ✅ **Timeouts HTTP** - Configuración de timeouts para peticiones
- ✅ **Modo de prueba mejorado** - Mejor manejo del modo test

#### Manejo de Errores
- ✅ **Excepciones específicas** - Nueva clase `ValidationException`
- ✅ **Mensajes en español** - Todos los errores traducidos
- ✅ **Validación de entrada** - Validación exhaustiva antes del envío
- ✅ **Manejo de errores HTTP** - Mejor gestión de errores de red

#### Respuestas y Tracking
- ✅ **Métodos de utilidad** - `isSuccessful()`, `getMessageCount()`, etc.
- ✅ **Información detallada** - Métodos para obtener estadísticas de envío
- ✅ **Serialización** - Métodos `toArray()`, `toJson()`, `__toString()`
- ✅ **Estados de mensaje** - Verificación de estados (pending, rejected, etc.)

### 🔧 Mejoras

#### SmsUpMessage
- ✅ **Métodos de programación** - `sendInMinutes()`, `sendInHours()`, `sendNow()`
- ✅ **Métodos de codificación** - `gsm7()`, `unicode()`, `encoding()`
- ✅ **Cálculo de SMS** - `getSmsCount()` para estimar número de SMS
- ✅ **Validación integrada** - `validate()` para verificar completitud
- ✅ **Factory method** - `create()` para creación rápida

#### SmsUpManager
- ✅ **Arquitectura mejorada** - Separación de responsabilidades
- ✅ **Validación previa** - Validación antes de envío
- ✅ **Logging integrado** - Logs automáticos de éxito y error
- ✅ **Manejo de reintentos** - Configuración de reintentos automáticos
- ✅ **Soporte para enlaces** - Detección automática de mensajes con links

#### SmsUpResponse y SmsUpResponseMessage
- ✅ **Métodos de estado** - `isSuccessful()`, `hasError()`, `isPending()`
- ✅ **Estadísticas** - Contadores de mensajes exitosos/fallidos
- ✅ **Información detallada** - `getSummary()`, `getStatusInfo()`
- ✅ **Serialización** - Conversión a array/JSON

#### SmsUpChannel (Notifications)
- ✅ **Inyección de dependencias** - Mejor integración con el container
- ✅ **Detección automática** - Múltiples formas de obtener números de teléfono
- ✅ **Manejo de errores** - Excepciones específicas para notificaciones
- ✅ **Configuración automática** - Aplicación de valores por defecto

#### SmsUpServiceProvider
- ✅ **Configuración publicable** - `vendor:publish` para configuración
- ✅ **Compatibilidad versiones** - Soporte para diferentes versiones de Laravel
- ✅ **Registro mejorado** - Mejor registro de servicios y aliases
- ✅ **Validación de configuración** - Verificación de configuración al inicio

### 📚 Documentación

- ✅ **README.md actualizado** - Documentación completa en español
- ✅ **USAGE.md nuevo** - Guía detallada de uso con ejemplos
- ✅ **Comentarios en código** - Documentación completa en español
- ✅ **Ejemplos prácticos** - Casos de uso reales y avanzados

### 🔄 Cambios de Compatibilidad

#### Configuración
- ⚠️ **Nueva configuración** - Migración de `config/services.php` a `config/smsup.php`
- ⚠️ **Variables de entorno** - Nuevas variables `SMSUP_*` en lugar de `SMSUP_KEY`
- ⚠️ **Canal de notificaciones** - Cambio de `smsUp` a `smsup` (minúsculas)

#### API
- ✅ **Retrocompatibilidad** - Los métodos existentes siguen funcionando
- ✅ **Nuevos métodos** - Métodos adicionales sin romper la API existente
- ✅ **Mejores respuestas** - Objetos de respuesta más informativos

### 🐛 Correcciones

- ✅ **Manejo de JSON** - Mejor manejo de respuestas JSON malformadas
- ✅ **Validación de teléfonos** - Validación más robusta de números
- ✅ **Codificación de caracteres** - Mejor soporte para caracteres especiales
- ✅ **Timeouts** - Configuración de timeouts para evitar colgados
- ✅ **Memory leaks** - Optimizaciones de memoria

### 📦 Dependencias

#### Actualizadas
- `illuminate/*` - Soporte para Laravel 12.x
- `guzzlehttp/guzzle` - Soporte para Guzzle 7.x
- `nesbot/carbon` - Soporte para Carbon 3.x

#### Nuevas (dev)
- `phpunit/phpunit` - Para testing
- `orchestra/testbench` - Para testing con Laravel
- `mockery/mockery` - Para mocking en tests

### 🔧 Configuración de Migración

Para migrar desde la versión 1.x:

1. **Publicar nueva configuración:**
   ```bash
   php artisan vendor:publish --tag=smsup-config
   ```

2. **Actualizar variables de entorno:**
   ```env
   # Antes
   SMSUP_KEY=tu_clave
   
   # Ahora
   SMSUP_API_KEY=tu_clave
   SMSUP_DEFAULT_FROM=TuEmpresa
   SMSUP_TEST_MODE=false
   ```

3. **Actualizar notificaciones:**
   ```php
   // Antes
   public function via($notifiable) {
       return ['smsUp'];
   }
   
   // Ahora (recomendado)
   public function via($notifiable) {
       return ['smsup'];
   }
   ```

### 🎯 Próximas Características (Roadmap)

- 📱 **Soporte para MMS** - Envío de mensajes multimedia
- 📊 **Dashboard de estadísticas** - Panel de control para métricas
- 🔄 **Queue jobs** - Integración con colas de Laravel
- 📧 **Plantillas** - Sistema de plantillas para mensajes
- 🌍 **Internacionalización** - Soporte para múltiples idiomas
- 🧪 **Testing helpers** - Helpers para testing de SMS

---

## [1.x] - Versiones Anteriores

Las versiones 1.x proporcionaban funcionalidad básica de envío de SMS con soporte limitado para versiones de Laravel.

### Características de 1.x
- Envío básico de SMS
- Integración con Laravel Notifications
- Soporte para Laravel 5.5-11.x
- Configuración en `config/services.php`
- Funcionalidad básica de webhooks 