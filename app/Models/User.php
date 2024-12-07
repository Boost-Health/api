<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf('%s %s', $this->first_name, $this->last_name)
        );
    }

    public function bot(): HasOne
    {
        return $this->hasOne(BotUser::class);
    }

    public function telegram(): HasOne
    {
        return $this->hasOne(TelegramUser::class);
    }

    public static function createFilamentUser()
    {
        static::create([
            'first_name' => 'Boost',
            'last_name' => 'Admin',
            'email' => 'admin@boost.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return Str::endsWith($this->email, 'boost.com');
    }
}
