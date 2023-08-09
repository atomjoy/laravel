# Wysyłanie powiadomienia

```php
<?php

use App\Models\User;
use App\Notifications\Contracts\NotifyMessage;
use App\Notifications\DbNotify;
use Illuminate\Support\Facades\Route;

Route::get('/notification', function () {

	$user = User::first();

	$msg = new NotifyMessage(
		'Hello max your link_signup link.',
		['link_signup' => [
			'href' => 'https://example.com/signup',
			'text' => 'Sign Up'
		]]
	);

	$user->notify(new DbNotify($msg));
	$user->notifyNow(new DbNotify($msg));

	return 'Sent';
});
```
