# Mocking validation request
Testowanie zdarzenia i błędu podczas tworzenia użytkownika poprzez nadpisanie klasy walidacji danych.

## Klasa testu
```php
<?php

namespace Tests\Feature;

use Mockery;
use Mockery\MockInterface;
use App\Models\User;
use App\Events\RegisterUserError;
use App\Exceptions\JsonException;
use App\Http\Controllers\RegisterController;
use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RegisterValidationTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function email_database_error()
	{
		Event::fake(RegisterUserError::class);

		$user = User::factory()->make();

		// Invalid User::create() validation data without password
		// will throw error event and exception from controller index method
		$valid = [
			'name' => 'Alex',
			'email' => 'user@laravel.com',
			// Tests mocking error
			// 'password' => 'Password123#',
		];

		// Mock validation request method validated
		$request = $this->instance(
			RegisterRequest::class,
			Mockery::mock(RegisterRequest::class, static function (MockInterface $mock) use ($valid) {
				// Add all functions used in controller from  RegisterRequest class
				$mock->shouldReceive('validated')->andReturn($valid);
				// Tests mocking error
				// $mock->shouldReceive('testDatabase')->andThrow(new Exception());
				// Tests mocking no error
				// $mock->shouldReceive('testDatabase')->andReturn(true);
			})
		);

		// Build controller
		$controller = $this->controller();

		try {
			// Call custom controller method
			$response = $this->app->call([$controller, 'index'], [
				'request' => $request,
			]);
		} catch (Exception $e) {
			// Catch exception
			$this->assertEquals($e->getMessage(), 'The account has not been created.');
		}

		// Then catch event
		Event::assertDispatched(RegisterUserError::class, function ($e) use ($valid) {
			return $valid == $e->valid;
		});

		// Call anonymous controller method
		$response = $this->app->call($controller, [
			'request' => $request,
		]);

		$this->assertSame($valid, $request->validated());
		$this->assertSame($valid, $response);
	}

	protected function controller(): RegisterController
	{
		return new class extends RegisterController
		{
			public function __invoke(RegisterRequest $request): array
			{
				return $request->validated();
			}
		};
	}
}
```

## Klasa kontrolera
```php
<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Events\RegisterUser;
use App\Events\RegisterUserError;
use App\Exceptions\JsonException;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
	function index(RegisterRequest $request)
	{
		$valid = $request->validated();

		try {
			// Tests mocking error
			// $request->testDatabase();

			// Create user
			$user = User::create([
				'name' => $valid['name'],
				'email' => $valid['email'],
				'password' => Hash::make($valid['password']),
				'username' => uniqid('user.'),
				'ip' => request()->ip(),
				'code' => uniqid()
			]);

			RegisterUser::dispatch($user);
		} catch (Exception $e) {
			report($e);
			RegisterUserError::dispatch($valid);
			throw new JsonException('The account has not been created.', 422);
		}

		return response()->json([
			'message' => 'Account has been created, please confirm your email address.',
			'created' => true
		], 201);
	}
}
```

## Klasa validatora
```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return true; // Allow all
	}

	public function rules()
	{
		$email = 'email:rfc,dns';
		if (env('APP_DEBUG') == true) {
			$email = 'email';
		}

		return [
			'name' => 'required|min:3|max:50',
			'email' => [
				'required', $email, 'max:191',
				Rule::unique('users')->whereNull('deleted_at')
			],
			'password' => [
				'required',
				Password::min(11)->letters()->mixedCase()->numbers()->symbols(),
				'confirmed',
				'max:50',
			],
			'password_confirmation' => 'required'
		];
	}

	public function failedValidation(Validator $validator)
	{
		throw new ValidationException($validator, response()->json([
			'message' => $validator->errors()->first()
		], 422));
	}

	function prepareForValidation()
	{
		$this->merge(
			collect(request()->json()->all())->only(['name', 'email', 'password', 'password_confirmation'])->toArray()
		);
	}

	public function testDatabase()
	{
		// Mock request method and throw error in controller if needed from tests
	}
}
```
