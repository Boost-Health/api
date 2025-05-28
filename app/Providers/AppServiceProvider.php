<?php

namespace App\Providers;

use App\Clients\OpenMRSClient;
use App\Clients\SlackBotClient;
use App\Services\ConversationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ConversationService::class, fn ($app) => new ConversationService);
        $this->app->singleton(OpenMRSClient::class, fn ($app) => new OpenMRSClient(config('services.open-mrs')));
        $this->app->singleton(SlackBotClient::class, fn ($app) => new SlackBotClient(config('services.slack.bot')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
