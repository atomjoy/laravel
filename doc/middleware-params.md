# Parametry middleware w Laravel
Przekazywanie parametrów do middleware z routes.

## Przekazywanie parametru dla aliasu middleware

```php
Route::prefix('web/api')->name('web.api.')
	->middleware(['auth', 'auth-roles:admin|worker'])
	->group(function () {
		Route::resource('password', PasswordController::class);
	});
  
```

### Pobierania parametru z aliasu

```php
public function handle($request, Closure $next, $role = '')
{
	$roles = array_filter(explode('|', $role));

	if (!empty($roles)) {
		if (Auth::check()) {
			$user = Auth::user();
			// if (!in_array($user->role->value, $roles)) {
			if (!in_array($user->role, $roles)) {
				throw new AuthenticationException("Unauthorized Role.");
			}
		} else {
			throw new AuthenticationException("Unauthorized User.");
		}
	}

	return $next($request);
}
```

## Przykłady middleware

### Zmiana języka z sesji

```php
public function handle($request, Closure $next)
{
	$lang =  session('locale', config('app.locale'));
	app()->setLocale($lang);
	
	if ($request->has('locale')) {
		app()->setLocale($request->query('locale'));
	}
	
	return $next($request);
}
```

### Dodaj nagłówek accept json

```php
public function handle($request, Closure $next)
{
	if ($request->is('web/api/*')) {
		$request->headers->set('Accept', 'application/json');
	}

	return $next($request);
}
```

### Sprawdzanie nagłówka

```php
public function handle(Request $request, Closure $next): Response
{
	// Require json header 'Accept: application/json'
	if ($request->is('web/api/*') && !$request->wantsJson()) {
		throw new JsonException('Not Acceptable.', 406);
	}
	
	return $next($request);
}
```

## Dodaj middleware
Dodaj w app/Http/Kernel.php

```php
<?php

// In aliases
protected $middlewareAliases = [
 'auth-role' => \App\Http\Middleware\AuthRoleMiddleware::class,
];

// Or in (opcja)
protected $routeMiddleware = [
 'auth-role' => \App\Http\Middleware\AuthRoleMiddleware::class,
];
```

### Użyj aliasu midlleware
Dodaj w routes/web.php

```php
<?php

Route::prefix('web/api')->name('web.api.')->middleware(['web', 'auth', 'auth-role:user|admin|worker'])->group(function () {
	// Routes here
});
```
