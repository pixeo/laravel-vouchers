<?php

declare(strict_types=1);

namespace FrittenKeeZ\Vouchers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use FrittenKeeZ\Vouchers\Console\Commands\MigrateCommand;
use Illuminate\Support\ServiceProvider;

class VouchersServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(Filesystem $filesystem): void
    {
        $this->publishes([$this->getPublishConfigPath() => config_path('vouchers.php')], 'config');
        $this->publishes([$this->getPublishMigrationsPath() => database_path('migrations')], 'migrations');

        $this->publishes([
            __DIR__.'/../database/migrations/create_voucher_tables.php.stub' => $this->getMigrationFileName($filesystem),
        ], 'migrations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->getPublishConfigPath(), 'vouchers');

        $this->app->bind('vouchers', function () {
            return new Vouchers();
        });
    }

    /**
     * Get publish config path.
     *
     * @return string
     */
    protected function getPublishConfigPath(): string
    {
        return __DIR__ . '/../publishes/config/vouchers.php';
    }

    /**
     * Get publish migrations path.
     *
     * @return string
     */
    protected function getPublishMigrationsPath(): string
    {
        return __DIR__ . '/../publishes/migrations/';
    }

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem): string
    {
        $timestamp = date('Y_m_d_His');

        return Collection::make($this->app->databasePath().DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem) {
                return $filesystem->glob($path.'*_create_voucher_tables.php');
            })->push($this->app->databasePath()."/migrations/{$timestamp}_create_voucher_tables.php")
            ->first();
    }
}
