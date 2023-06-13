# Parametry middleware w Laravel
Przekazywanie parametrów do klasy middleware z routes group middleware.

## Przekazywanie parametrów dla klasy middleware

```php
<?php

// String
Route::prefix('web/api')->name('web.api.')
	->middleware(['auth', AcceptJsonMiddleware::class, 'role' => 'admin|worker'])
	->group(function () {
		Route::resource('password', PasswordController::class);
	});

// Json
Route::prefix('web/api')->name('web.api.')
	->middleware(['auth', AcceptJsonMiddleware::class, 'role' => json_encode(['admin', 'worker'])])
	->group(function () {
		Route::resource('password', PasswordController::class);
	});
```

### Pobierz parametry w middleware

```php
public function handle(Request $request, Closure $next): Response
{
  // String 
  $actions = $request->route()->getAction('middleware.role');
  $roles = array_filter(explode('|', $actions));
  
  // Json
  $roles = json_decode($request->route()->getAction('middleware.role'));

	return $next($request);
}
```

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
				if (!in_array($user->role->value, $roles)) {
					throw new AuthenticationException("Unauthorized Role.");
				}
			} else {
				throw new AuthenticationException("Unauthorized User.");
			}
		}

		return $next($request);
}
