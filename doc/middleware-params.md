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
