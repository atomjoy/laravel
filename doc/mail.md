# Mail w Laravel
Wysyłanie wiadomości email w Laravel.

### Wysyłanie wiadomości email z kontrolera

```php
try {
  // or only email address 'User <user@laravel.com>'
  $user = User::find(1);
  Mail::to($user)->locale(app()->getLocale())->send(new RegisterMail($user));
} catch (Exception $e) {
  report($e);
  throw new JsonException('The activation email could not be sent, please try to reset your password.');
}
```

### Wysyłanie wiadomości email z terminala

```php
php artisan tinker

Mail::raw('Hello World!', function($msg) {$msg->to('<user@laravel.com>')->subject('Test Email'); });
```
