# SmsUp API Integration for Laravel

## Installation

### Install Package
You can install this package via composer:
```bash
composer require squareetlabs/laravel-smsup
```

### Add Service Provider & Facade
#### For Laravel 5.5+
Once the package is added, the service provider and facade will be autodiscovered.

#### For Older versions of Laravel
Add the ServiceProvider to the providers array in `config/app.php`:
```php
SquareetLabs\LaravelSmsUp\SmsUpServiceProvider::class,
```

Add the Facade to the aliases array in `config/app.php`:
```php
'SmsUp': SquareetLabs\LaravelSmsUp\Facades\SmsUp::class,
```

## Configuration
Add your SmsUp API key to your `config/services.php` file:
```php
return [   
    ...
    ...
    'smsUp' => [
         'key' => env('SMSUP_KEY'),
         'test_mode' => true
    ]
    ...
```

## Usage

### Using Laravel Notification
Use artisan to create a notification:
```bash
php artisan make:notification someNotification
```
Return `[smsUp]` in the `public function via($notifiable)` method of your notification:
```php
public function via(INotifiable $notifiable)
{
    return ['smsUp'];
}
```
Add the method `public function toSmsUp($notifiable)` to your notification, and return an instance of `SmsUpMessage`:
```php
use SquareetLabs\LaravelSmsUp\SmsUpMessage
...
public function ToSmsUp(INotifiable $notifiable)
{
    $message = new SmsUpMessage();
    $message->to('34666666666') 
        ->from('Foo')
        ->text('Text of your message')
        ->custom('MyMsgID-12345') // Optional. 
        ->link('http://www.google.com'); // Optional

    return $message;
}
```
If you don't indicate the parameter `to`, make sure your notifiable entity has `routeNotificationForSmsUp` method defined:
```php
/**
 * Route notifications for the SmsUp channel.
 *
 * @return string
 */
public function routeNotificationForSmsUp(): string
{
    return $this->phone;
}
```

### Using SmsUp Facade

