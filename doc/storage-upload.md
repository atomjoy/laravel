# Wysyłanie plików (avatar upload) w Laravel

## kontroler

```php
<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadAvatarRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadAvatarController extends Controller
{
	function index(UploadAvatarRequest $request)
	{
		try {
			$filename = $request->user()->id . '.webp';

			$path = $request->file('avatar')->storeAs('avatars', $filename, 'public');

			// $path = Storage::disk('public')->putFileAs('avatars', $request->file('avatar'), $filename);

			return response()->json([
				'message' => __('apilogin.upload.avatar.success'),
				'avatar' => $path,
			], 200);
		} catch (Exception $e) {
			report($e);
			return response()->json([
				'message' => __('apilogin.upload.avatar.error'),
				'avatar' => null
			], 422);
		}
	}
}
```

## Walidacja

```php
<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UploadAvatarRequest extends FormRequest
{
	protected $stopOnFirstFailure = true;

	public function authorize()
	{
		return Auth::check(); // Allow logged
	}

	public function rules()
	{
		return [
			'avatar' => [
				'required',
				'mimes:webp',
				Rule::dimensions()->minWidth(64)->minHeight(64),
				Rule::dimensions()->maxWidth(1025)->maxHeight(1025),
				Rule::file()->types(['webp'])->max(config('app.max_upload_size_mb', 5) * 1024),
			]
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
			collect(request()->json()->all())->only(['avatar'])->toArray()
		);
	}
}
```

## Testy

```php
<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadAvatarTest extends TestCase
{
	use RefreshDatabase;

	/** @test */
	function upload_avatar()
	{
		Storage::fake('public');

		$user = User::factory()->create([
			'name' => 'Alex',
			'email' => uniqid() . '@gmail.com'
		]);

		$this->assertDatabaseHas('users', [
			'name' => $user->name,
			'email' => $user->email,
		]);

		$this->actingAs($user);

		$response = $this->postJson('/web/api/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.webp'),
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'The avatar field has invalid image dimensions.',
		]);

		$response = $this->postJson('/web/api/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.png'),
		]);

		$response->assertStatus(422)->assertJson([
			'message' => 'The avatar field must be a file of type: webp.',
		]);

		$response = $this->postJson('/web/api/upload/avatar', [
			'avatar' => UploadedFile::fake()->image('avatar.webp', 200, 200),
		]);

		$response->assertStatus(200)->assertJson([
			'message' => 'Avatar has been uploaded.',
			'avatar' => 'avatars/' . $user->id . '.webp'
		]);

		// Assert one or more files were stored...
		Storage::disk('public')->assertExists('avatars/' . $user->id . '.webp');
	}
}
```

## Links
- https://laravel.com/docs/10.x/filesystem#file-uploads
- https://laravel.com/docs/10.x/validation#basic-usage-of-mime-rule
