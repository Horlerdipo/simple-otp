<?php

namespace Horlerdipo\SimpleOtp;

use Horlerdipo\SimpleOtp\Commands\PruneExpiredOtpCommand;
use Horlerdipo\SimpleOtp\Contracts\OtpContract;
use Illuminate\Foundation\Application;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SimpleOtpServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('simple-otp')
            ->hasConfigFile()
            ->hasMigration('create_otps_table')
            ->hasViews()
            ->hasCommand(PruneExpiredOtpCommand::class);

        $this->app->bind(OtpContract::class, function (Application $app) {
            return new SimpleOtpManager($app);
        });
    }
}
