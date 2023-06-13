# Enum w Laravel
Wyliczenia php w Laravel.

## Utwórz interfejs

```php
<?php

namespace App\Enums;

interface HasRole
{
	public static function fromString(string $value): UserRole;
	public static function toArray(): array;
	public function role(): string;
}
```

### Utwórz klasę enums

```php
<?php

namespace App\Enums;

enum UserRole: string implements HasRole
{
	case USER = 'user';
	case ADMIN = 'admin';
	case WORKER = 'worker';

	/**
	 * Convert string to UserRole
	 *
	 * @param string $value
	 */
	public static function fromString(string $value): UserRole
	{
		return self::from($value);
	}

	/**
	 * Convert enum to array
	 */
	public static function toArray(): array
	{
		return array_column(self::cases(), 'name');
	}

	/**
	 * Convert UserRole to string (serialize)
	 * Or use UserRole::USER->value
	 */
	public function role(): string
	{
		return match ($this) {
			self::USER => 'user',
			self::ADMIN => 'admin',
			self::WORKER => 'worker',
		};
	}
}
```

## Laravel model casts enums

```php
<?php

namespace App\Models;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Database\Factories\UserFactory;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

class User extends Authenticatable implements HasLocalePreference
{
	use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

	protected $fillable = [
		'name',
		'email',
		'password',
		'email_verified_at',
		'remember_token',
		'newsletter_on',
		'mobile_prefix',
		'mobile',
		'username',
		'location',
		'website',
		'locale',
		'image',
		'role',
		'code',
		'ip',
	];

	protected $hidden = [
		'code',
		'password',
		'remember_token',
	];

	protected $casts = [
		'role' => UserRole::class,
	];

	protected static function newFactory()
	{
		return UserFactory::new();
	}

	protected function serializeDate(\DateTimeInterface $date)
	{
		return $date->format('Y-m-d H:i:s');
	}

	public function preferredLocale()
	{
		return $this->locale;
	}
	
	function scopeHasRole($query, UserRole $role = UserRole::USER)
	{
		return $query->where('role', $role);
	}

	function isAdmin()
	{
		return ($this->role === UserRole::ADMIN);
	}
}
```
