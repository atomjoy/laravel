# Singletonowe kontrolery zasobów
Kontroler dla relacji One-to-One w Laravel (Singleton Resource Controllers).

## Utwórz route

```php
<?php

Route::prefix('web/api')->name('web.api.')->middleware(['web'])->group(function () {
	// Private routes
	Route::middleware(['auth'])->group(function () {
		// Resource
		Route::singleton('address', AddressController::class, ['except' => ['edit']]); // removed form edit url
	});
});
```

## Kontroler

```php
<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAddressRequest;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
	/**
	 * Display the specified resource.
	 */
	public function show()
	{
		$user = User::with('address')->findOrFail(Auth::id());

		return response()->json([
			'address' => $user->address
		], 200);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(UpdateAddressRequest $request)
	{
		$valid = $request->validated();
		try {
			$user = User::with('address')->findOrFail(Auth::id());

			$user->address()->updateOrCreate([
				'user_id' => $user->id
			], $valid);

			return response()->json([
				'message' => __("apilogin.address.success"),
				'address' => $user->fresh(['address'])->address
			], 200);
		} catch (Exception $e) {
			report($e);
			throw new Exception(__("apilogin.address.error"), 422);
		}
	}
}
```

## Walidacja danych

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class UpdateAddressRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return Auth::check(); // logged only
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array
	{
		return [
			'country' => 'sometimes|max:50',
			'state' => 'sometimes|max:50',
			'city' => 'sometimes|max:50',
			'street' => 'sometimes|max:50',
			'postal_code' => 'sometimes|max:50',

			// Update with unique fields, example user profiles table
			// 'unique:profiles,username,' . Auth::user()->profile?->id ?? null,
			// Rule::unique('profiles')->ignore(Auth::user()->profile?->id ?? null),
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
		$this->merge([
			collect(request()->json()->all())->only([
				'country', 'state', 'city', 'street', 'postal_code'
			])->toArray()
		]);
	}
}
```

## Zobacz trasy

```sh
php artisan route:list
```

## Linki

https://laravel.com/docs/10.x/controllers#singleton-resource-controllers
