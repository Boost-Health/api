<?php

namespace App\Models;

use App\Clients\SlackBotClient;
use App\Enums\UserType;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
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
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'meta' => 'json',
        'type' => UserType::class,
    ];

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
        static::updateOrCreate(['email' => 'admin@boost.com'], [
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

    public static function availableDoctor(): ?self
    {
        return static::whereType(UserType::DOCTOR)->whereIsAvailable(true)->inRandomOrder()->first();
    }

    public function isBot(): bool
    {
        return $this->type === UserType::BOT;
    }

    public function isDoctor(): bool
    {
        return $this->type === UserType::DOCTOR;
    }

    public function inviteToSlackChannel(User $guest): void
    {
        if (blank($guest->slack_user_id)) {
            throw new \RuntimeException('Guest does not have a slack user id', ['guest' => $guest]);
        }

        $response = app(SlackBotClient::class)->patientInvite($this, $guest);

        if ($response->failed()) {
            Log::error("slack:invite:{$this->id}:{$guest->id}:failed", $response->json());
        }
    }
}
