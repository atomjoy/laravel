# Powiadomienia

```sh
php artisan notifications:table

php artisan migrate

php artisan make:notification NotifyMessage
```

## Klasa User

```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
 use HasApiTokens, HasFactory, Notifiable;

 /**
  * Prefer log notification.
  */
 public $prefers_log = false;

  /**
  * Notification toMail recipient.
  */
  public function routeNotificationForMail (Notification $notification) :array|string {
    return [
        // Return email address only
        return $this->email;

        // Return email address and name
        return [$this->email => $this->name];
    ];
  }

  /**
  * Notification custom chanel toLog identifier.
  */
  public function routeNotificationForLog ($notifiable) {
    return 'identifier-for-log: ' . $this->id;
  }
}
```

### Utwórz powiadomienie

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotifyMessage extends Notification
{
 use Queueable;

 /**
  * Create a new notification instance.
  */
 public function __construct(
  protected $message
 ) {
 }

 /**
  * Get the notification's delivery channels.
  *
  * @return array<int, string>
  */
 public function via(object $notifiable): array
 {
    // Custom log channel or standard database
    return $notifiable->prefers_log ? ['log'] : ['database'];
    // return ['mail'];
 }

 /**
  * Get the mail representation of the notification.
  */
 public function toMail(object $notifiable): MailMessage
 {
  return (new MailMessage)
    ->line('The introduction to the notification.')
    ->action('Notification Action', url('/'))
    ->line('Thank you for using our application!')
    ->from('barrett@example.com', 'Barrett Blair');
    // Get recipient email from routeNotificationForMail or manualy
    // ->to($notifiable->email);

  return (new MailMessage)->view(
    'emails.name', ['message' => $this->message]
  );
 }

 /**
  * Get the array representation of the notification.
  *
  * @return array<string, mixed>
  */
 public function toDatabase(object $notifiable): array
 {
  return [
    'user_id' => $notifiable->id,
    'message' => $this->message,
  ];
 }

 /**
  * Get the array representation of the notification.
  *
  * @return array<string, mixed>
  */
 public function toArray(object $notifiable): array
 {
  return [
    'user_id' => $notifiable->id,
    'message' => $this->message,
  ];
 }
}

```

### Wyślij powiadomienie

```php
Route::get('/', function () {
 $user = User::first();
 $user->notify(new NotifyMessage('Hello Max !!!'));
 $user->notifyNow(new NotifyMessage('Hello Max !!!'));
});
```

## Niestandardowa powiadomienia

### Utwórz klasę nowego kanału
```php
<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;

class LogChannel
{
    public function send ($notifiable, Notification $notification) {

        if (method_exists($notifiable, 'routeNotificationForLog')) {
            $id = $notifiable->routeNotificationForLog($notifiable);
        } else {
            $id = $notifiable->getKey();
        }

        $data = method_exists($notification, 'toLog')
            ? $notification->toLog($notifiable)
            : $notification->toArray($notifiable);

        if (empty($data)) {
            return;
        }

       app('log')->info(json_encode([
            'id'   => $id,
            'data' => $data,
        ]));        return true;
    }
}
```

### Zarejestruj klasę

```php
<?php
namespace App\Providers;

use App\Channels\LogChannel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
      // Register custom notification channel
      Notification::extend('log', function ($app) {
        return new LogChannel();
      });
    }
}
```

### Utwórz klasę powiadomień

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LogNotification extends Notification
{
    use Queueable;

    public function __construct (
      protected $message = ''
    ) { }

    public function via ($notifiable) {
      // The name we used when registering in the provider
      return [ 'log' ];

      // If not registered in service provider
      // return [ \App\Channels\LogChannel::class ];
    }

    public function toLog ($notifiable) {
      return [
        'from'          => 'to-log',
        'notifiable-id' => $notifiable->id,
        'message' => $this->message,
      ];
    }

    public function toArray ($notifiable) {
      return [
        'from'          => 'to-array',
        'notifiable-id' => $notifiable->id,
        'message' => $this->message,
      ];
    }
}
```

### Wyślij niestandardowe powiadomienie

```php
Route::get('/', function () {
  $user = User::first();

  $user->notify(new LogNotification());
  $user->notifyNow(new LogNotification());

  Notification::send($user, new LogNotification());
  Notification::sendNow($user, new LogNotification());
});
```

## Links

- <https://laravel.com/docs/10.x/notifications#mailables-and-on-demand-notifications>
- <https://medium.com/@sirajul.anik/laravel-notifications-part-2-creating-a-custom-notification-channel-6b0eb0d81294>
- <https://www.honeybadger.io/blog/php-laravel-notifications/>
- <https://www.scratchcode.io/laravel-notification-tutorial-with-example/>