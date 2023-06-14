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
