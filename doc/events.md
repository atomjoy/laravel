# Zdarzenia (events) w Laravel

## Wysyłanie zdarzenia

```php
<?php
use Illuminate\Support\Facades\Event;

Event::dispatch('webi.user.created', User::first());
```

## Przechwytywanie zdarzenia
app/Providers/eventServiceProvider.php

```php
<?php
use Illuminate\Support\Facades\Event;

public function boot(): void
{
  Event::listen('webi.user.created', function ($event) {
  	// Do something with event
    // dd($event);
  });
}
```

## Package event provider
Register in package service provider with $this->register(EventServiceProvider::class) method.
```php
<?php

namespace Webi\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Webi\Events\UserCreated;
use Webi\Listeners\UserCreatedNotification;

class EventServiceProvider extends ServiceProvider
{
	protected $listen = [
		UserCreated::class => [
			UserCreatedNotification::class,
		]
	];

	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();
 
		Event::listen('webi.user.created', function ($user) {
			// Do somthing ...
		});
	}
}
```

## Dla klas przykłady
<https://laravel.com/docs/10.x/events#generating-events-and-listeners>
