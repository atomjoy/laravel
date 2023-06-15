# Wysyłanie wiadomości email z testu w Laravel

## Hmailserver
Dodaj domeny i adresy email user@laravel.com, user@app.xx

### Wysyłaj email podczas testów
Zmień w phpunit.xml

```xml
<php>
  <env name="APP_ENV" value="testing"/>
  <env name="MAIL_MAILER" value="smtp"/>
  <!-- <env name="MAIL_MAILER" value="array"/> -->
</php>
```

### Konfiguracja local smtp (hmailserver)
Zmień w .env

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=25
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="user@app.xx"
MAIL_FROM_NAME="${APP_NAME}"
```

## Kontroler

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

## Wysyłanie wiadomości email z terminala

```php
php artisan tinker

Mail::raw('Hello World!', function($msg) {$msg->to('<user@laravel.com>')->subject('Test Email'); });
```
