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
Set `test_mode` to `true` if you want to simulate submitting messages, it's perfect for testing and debugging, it has no cost.

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
use SquareetLabs\LaravelSmsUp\SmsUpMessage;
...
public function ToSmsUp(INotifiable $notifiable)
{
    $message = new SmsUpMessage();
    $message->to('34xxxxxxxxx') 
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
To include the `link` in the message you must put the tag `{LINK}` in the area of the text of the sms you want.

example:
```php
->text('Hi John! See our new offers only available for you: {LINK}');
```

### Using SmsUp Facade

#### Send messages
```php
use SquareetLabs\LaravelSmsUp\SmsUpMessage;
use SquareetLabs\LaravelSmsUp\Facades\SmsUp;
...
$message1 = new SmsUpMessage();
$message->to('34xxxxxxxxx') 
    ->from('Foo')
    ->text('Text of your message')
    ->custom('MyMsgID-12345') // Optional. 
    ->link('http://www.google.com'); // Optional
$message2 = new SmsUpMessage();
$message2->to('34xxxxxxxxx') 
    ->from('Foo')
    ->text('Text of your message')
    ->custom('MyMsgID-12346') // Optional. 
    ->link('http://www.google.com'); // Optional
$messages = [
    $message1->formatData(),
    $message2->formatData()
];
SmsUp::sendMessages($messages);
```

#### Get SmsUp account balance
```php
use SquareetLabs\LaravelSmsUp\Facades\SmsUp;
...
$balance = SmsUp::getBalance();
```

#### Verify phone number by SmsUp
This method return `true` or `false`. This service has a cost charged by SmsUp.
```php
use SquareetLabs\LaravelSmsUp\Facades\SmsUp;
...
$verify = SmsUp::verifyPhone('34xxxxxxxxx');
```

## Available Events
LaravelSmsUp comes with handy events which provides the required information about the SMS messages.

### Messages Was Sent
Triggered when one or more messages are sent.

Example:
```php
use SquareetLabs\LaravelSmsUp\Events\SmsUpMessageWasSent;
use SquareetLabs\LaravelSmsUp\SmsUpMessage;
use SquareetLabs\LaravelSmsUp\SmsUpResponse;
use SquareetLabs\LaravelSmsUp\SmsUpResponseMessage;

class SmsUpMessageSentListener
{
    /**
     * Handle the event.
     *
     * @param  SmsUpMessageWasSent  $event
     * @return void
     */
    public function handle(SmsUpMessageWasSent $event)
    {
        $response = $event->response; // Class SmsUpResponse
        $message = $event->message; // Class SmsUpMessage

        if ($response->getStatus() != 'ok') {
            $yourModel = YourModel::find($message->getCustom());
            $yourModel->sms_status = $response->getStatus();
            $yourModel->sms_error_id = $response->getErrorId();
            $yourModel->sms_error_msg = $response->getErrorMsg();
            $yourModel->save();
        } else {
            foreach ($response->getResult() as $responseMessage) { // class SmsUpResponseMessage
                $yourModel = YourModel::find($responseMessage->getCustom());
                $yourModel->sms_status = $responseMessage->getStatus();
                $yourModel->sms_id = $responseMessage->getSmsId();
                $yourModel->sms_error_id = $responseMessage->getErrorId();
                $yourModel->sms_error_msg = $responseMessage->getErrorMsg();
                $yourModel->save();
            }
        }
    }
}
```
In your `EventServiceProvider`:
````php
protected $listen = [
        ...
        'SquareetLabs\LaravelSmsUp\Events\SmsUpMessageWasSent' => [
            'App\Listeners\SmsUpMessageSentListener',
        ],
    ];
````

### SmsUp Report Received
Triggered when a status report of sent sms is received from SmsUp.
The callback url passed to SmsUp is: `http://yourserver/yourapplication/smsup/report`.

Example:
```php
use SquareetLabs\LaravelSmsUp\Events\SmsUpReportWasReceived;
use SquareetLabs\LaravelSmsUp\SmsUpReportResponse;
use SquareetLabs\LaravelSmsUp\SmsUpReportResponseMessage;

class SmsUpReportReceivedListener
{
    /**
     * Handle the event.
     *
     * @param  SmsUpReportWasReceived  $event
     * @return void
     */
    public function handle(SmsUpReportWasReceived $event)
    {
        $response = $event->response; // Class SmsUpReportResponse
        
        foreach ($response->getResponseMessages() as $responseMessage) { // Class SmsUpReportResponseMessage
            $yourModel = YourModel::find($responseMessage->getCustom());
            $yourModel->sms_status = $responseMessage->getStatus();
            $yourModel->sms_delivery_at = $responseMessage->getDlrDate();
            $yourModel->save();
        }
    }
}
```
In your `EventServiceProvider`:
````php
protected $listen = [
        ...
        'SquareetLabs\LaravelSmsUp\Events\SmsUpReportWasReceived' => [
            'App\Listeners\SmsUpReportReceivedListener',
        ],
    ];
````

## SmsUp API Documentation
Visit [SmsUp API Documentation](https://app.smsup.es/api/3.0/docs/) for more information.

## Support
Feel free to post your issues in the issues section.

## Credits
- [Alberto Rial Barreiro](https://github.com/alberto-rial)
- [Jacobo Cantorna Cigarrán](https://github.com/jcancig)
- [SquareetLabs](https://www.squareet.com)
- [All Contributors](../../contributors)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.