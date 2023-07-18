# Testy w Laravel
Testowanie aplikacji i wysyłanie wiadomości email z testu w Laravel.

## Localny server smtp
Dodaj domeny i adresy email user@laravel.com, user@app.xx do serwera poczty.

### Wysyłaj email podczas testów
Zmień w phpunit.xml

```xml
<php>  
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

## Dodaj pakiety do testów
Zmień w phpunit.xml

```xml
<testsuite name="Webi">
    <directory suffix="Test.php">./vendor/atomjoy/webi/tests</directory>
</testsuite>
```

### Uruchom testy
Z terminala w vscode

```sh
php artisan test --stop-on-failure
php artisan test --stop-on-failure --testsuite=Webi
```

## Mailable sprawdzenie tytułu i odbiorcy wiadomości email
Bez wysyłania wiadomości email.

```php
<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Mail\RegisterMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendEmailTest extends TestCase
{
	/**
	 * A basic test example.
	 */
	public function test_register_user(): void
	{
		Mail::fake();

		$email = uniqid() . '@laravel.com';

		$response = $this->postJson('web/api/register', [
			'name' => 'Alex',
			'email' => $email,
			'password' => 'Password123!',
			'password_confirmation' => 'Password123!',
		]);

		$response->assertStatus(201)->assertJsonMissing(['created' => false])->assertJson([
			'message' => 'Account has been created, please confirm your email address.',
			'created' => true,
		]);

		Mail::assertSent(RegisterMail::class, function ($mail) use ($email) {
			$mail->build();
			$this->assertEquals("👋 Account activation.", $mail->subject, 'The subject was not the right one.');
			return $mail->hasTo($email);
		});

		// $response->assertStatus(422)->assertJsonMissing(['created'])->assertJson([
		// 	'message' => 'The email has already been taken.'
		// ]);
	}
}
```
