# Migracje tabel w Laravel
Tworzenie i modyfikacja tabel w bazie danych.

## Wczytywanie migracj z subfolderu
W pliku app/Providers/AppServiceProvider.php

```php
public function boot(): void
{	
	$this->loadMigrationsFrom(database_path('migrations/posts'));
	// ...
}
```

## Tworzenie migracji

```sh
php artisan make:migration update_users_table
php artisan make:migration update_users_table --path=/database/migrations/posts
```

## Aktualizacja tabel w bazie danych

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			if (!Schema::hasColumn('users', 'email_verified_at')) {
				$table->timestamp('email_verified_at')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'username')) {
				$table->string('username', 30)->unique();
			}
			if (!Schema::hasColumn('users', 'role')) {
				$table->enum('role', ['user', 'worker', 'admin'])->nullable()->default('user');
			}
			if (!Schema::hasColumn('users', 'mobile_prefix')) {
				$table->string('mobile_prefix', 10)->nullable(true);
			}
			if (!Schema::hasColumn('users', 'mobile')) {
				$table->string('mobile', 30)->nullable(true);
			}
			if (!Schema::hasColumn('users', 'code')) {
				$table->string('code', 30)->unique()->nullable(true);
			}
			if (!Schema::hasColumn('users', 'locale')) {
				$table->string('locale', 2)->nullable()->default(config('app.locale'));
			}
			if (!Schema::hasColumn('users', 'ip')) {
				$table->string('ip')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'remember_token')) {
				$table->string('remember_token')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'newsletter_on')) {
				$table->tinyInteger('newsletter_on')->nullable(true)->default(1);
			}
			if (!Schema::hasColumn('users', 'image')) {
				$table->string('image')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'website')) {
				$table->string('website')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'location')) {
				$table->string('location')->nullable(true);
			}
			if (!Schema::hasColumn('users', 'deleted_at')) {
				$table->softDeletes();
			}
			if (!Schema::hasColumn('users', 'provider')) {
				$table->string('provider')->nullable(true);
			}
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn([
				'username', 'role', 'mobile_prefix', 'mobile',
				'code', 'locale', 'ip', 'newsletter_on',
				'image', 'website', 'location'
			]);
		});
	}
};
```

## Klucze obce referencje

```php
// Add
Schema::table('posts', function (Blueprint $table) {
	// Old style
	$table->unsignedBigInteger('user_id')->nullable(); 
	$table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

	// One line
	$table->foreignId('foreign_id')->nullable()->constrained("users")->cascadeOnUpdate()->nullOnDelete();
});

// Remove
Schema::table('users', function (Blueprint $table) {
	$table->dropForeign(['user_id']);
});
```

## Wyłączanie sprawdzania kluczy

```php
// Disable
Schema::disableForeignKeyConstraints();

// Do something ... delete record etc.

// Enable
Schema::enableForeignKeyConstraints();
```

## Uruchom migracje

```sh
php artisan migrate

php artisan migrate --force

php artisan migrate:refresh

php artisan migrate:refresh --seed
```
