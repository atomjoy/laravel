# Testowanie wysyłania wiadomości email w Laravel.
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
Bez wysyłania wiadomości email na dwa różne sposoby z klasą event i mail.

```php
<?php

namespace Tests\Feature;

use App\Mail\RegisterMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterTest extends TestCase
{
	use RefreshDatabase;

	/**
	 * Register user test with mail class.
	 */
	public function test_create_user(): void
	{
		Mail::fake();

		$name = 'Alex';
		$email = uniqid() . '@laravel.com';

		$response = $this->postJson('web/api/register', [
			'name' => $name,
			'email' => $email,
			'password' => 'Password123!',
			'password_confirmation' => 'Password123!',
		]);

		$response->assertStatus(201)->assertJsonMissing(['created' => false])->assertJson([
			'message' => 'Account has been created, please confirm your email address.',
			'created' => true,
		]);

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		Mail::assertSent(RegisterMail::class, function ($mail) use ($email, $name) {
			$mail->build();
			$html = $mail->render();

			// Name
			$this->assertTrue(strpos($html, $name) !== false);

			// Subject
			$this->assertEquals("👋 Account activation.", $mail->subject, 'The subject was not the right one.');

			// Activation link
			$this->assertMatchesRegularExpression('/\/activate\/[0-9]+\/[a-z0-9]+\?locale=[a-z]{2}"/i', $html);

			// Recipient
			return $mail->hasTo($email);
		});
	}

	/**
	 * Register user test with event class.
	 */
	public function test_create_user_event(): void
	{
		Event::fake([MessageSent::class]);

		$name = 'Alex';
		$email = uniqid() . '@laravel.com';

		$response = $this->postJson('web/api/register', [
			'name' => $name,
			'email' => $email,
			'password' => 'Password123!',
			'password_confirmation' => 'Password123!',
		]);

		$response->assertStatus(201)->assertJsonMissing(['created' => false])->assertJson([
			'message' => 'Account has been created, please confirm your email address.',
			'created' => true,
		]);

		$this->assertDatabaseHas('users', [
			'name' => $name,
			'email' => $email,
		]);

		Event::assertDispatched(MessageSent::class, function ($e) use ($email, $name) {
			$html = $e->message->getHtmlBody();

			// Name
			$this->assertStringContainsString($name, $html);

			// Activation link
			$this->assertMatchesRegularExpression('/\/activate\/[0-9]+\/[a-z0-9]+\?locale=[a-z]{2}"/i', $html);

			// Check password tag
			// $this->assertMatchesRegularExpression('/word>[a-zA-Z0-9#]+<\/pass/', $html);

			// Recipient
			return collect($e->message->getTo())->first()->getAddress() == $email;
		});
	}

	// Cut string from html 
	function getPassword($html)
	{
		preg_match('/word>[a-zA-Z0-9#]+<\/pass/', $html, $matches, PREG_OFFSET_CAPTURE);
		return str_replace(['word>', '</pass'], '', end($matches)[0]);
	}
}
```
