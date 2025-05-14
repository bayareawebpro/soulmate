<?php declare(strict_types=1);

namespace BayAreaWebPro\Soulmate;

use BayAreaWebPro\Soulmate\Providers\OllamaProvider;
use Illuminate\Support\ServiceProvider;

class SoulmateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
//        if($this->app->runningInConsole()){
//            $this->publishes([
//                __DIR__ . '/../config/soulmate.php' => config_path('soulmate.php'),
//            ], 'config');
//        }

        $this->app->bind(SoulmateService::class, SoulmateService::class);
    }

    /**
     * Get the services provided by the provider.
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            SoulmateService::class
        ];
    }
}
