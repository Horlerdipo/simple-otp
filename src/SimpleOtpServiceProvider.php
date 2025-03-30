<?php

namespace Horlerdipo\SimpleOtp;

use Horlerdipo\SimpleOtp\Commands\SimpleOtpCommand;
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
            ->hasMigration('create_simple_otp_table');
        //            ->hasCommand(SimpleOtpCommand::class);
    }
}
