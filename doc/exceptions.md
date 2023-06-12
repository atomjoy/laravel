# Wyjątki w Laravel
Przechwytywanie wyjątków/błędów w Laravel (exceptions).

## Utwórz klasę wyjątku

```sh
php artisan make:exception JsonException
```

### Edytuj klasę wyjątku

```php
<?php

namespace App\Exceptions;

use Exception;

class JsonException extends Exception
{
	/**
	 * Report the exception.
	 *
	 * @return bool|null
	 */
	public function report()
	{
		// Enable default logging
		return false;
	}

	/**
	 * Render the exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function render($request)
	{
		// Require json header 'Accept: application/json'
		// if ($request->is('web/api/*') && !$request->wantsJson()) {
		// 	$this->message = 'Not Acceptable.';
		// 	$this->code = 406;
		// }

		// Create json response
		return response()->json([
			'message' => $this->message ?? 'Unknown Exception.',
		], ($this->code >= 100 && $this->code < 600) ? $this->code : 422);
	}
}
```

### Utwórz klasę do obsługi wyjątków

```php
<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use PDOException;
use Exception;
use Throwable;

class CatchError
{
	static function from(Exception|Throwable $e)
	{
		if ($e instanceof PDOException) {
			throw new JsonException('Database Error.', 500);
		}

		if ($e instanceof ModelNotFoundException) {
			throw new JsonException('Model Not Found.', 422);
		}

		// Add more ...

		throw new JsonException($e->getMessage(), $e->getCode());
	}
}
```

### Utwórz kontroler

```php
<?php

namespace App\Http\Controllers;

use App\Exceptions\CatchError;
use App\Models\User;
use Exception;

class SampleController extends Controller
{
	public function index()
	{
		try {
			// User::findOrFail(0);      
			// User::where('id', 11)->update(['xxx' => 1]);
			throw new Exception('Your Error Message.', 422);
		} catch (Exception $e) {
			CatchError::from($e);
		}
	}
}
```

## Przechwytywanie wszystkich wyjątków (opcja)

W ***app/Exceptions/Handler.php*** można przechwytywać wyjątki z całej aplikacji Laravel z podziałem na klasy.

```php
<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
	/**
	 * The list of the inputs that are never flashed to the session on validation exceptions.
	 *
	 * @var array<int, string>
	 */
	protected $dontFlash = [
		'current_password',
		'password',
		'password_confirmation',
	];

	/**
	 * Register the exception handling callbacks for the application.
	 */
	public function register(): void
	{
		$this->reportable(function (Throwable $e) {
			//
		});

		// Catch exceptions for class
		$this->renderable(function (Exception $e, $request) {
			if ($request->is('web/api/*') && $request->wantsJson()) {
				return response()->json([
					'message' => $e->getMessage(),
				], 422);
			}
		});

		// Catch all
		$this->renderable(function (Throwable $e, $request) {
			// Check json header
			if ($request->is('web/api/*') && !$request->wantsJson()) {
				return response()->json([
					'message' => 'Not Acceptable.',
					'description' => 'Accept: application/json header required.'
				], 406);
			}
		});
	}
}
```
