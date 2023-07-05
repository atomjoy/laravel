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
