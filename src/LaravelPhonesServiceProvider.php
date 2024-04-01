<?php

namespace Hichxm\LaravelPhones;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelPhonesServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-phones')
            ->hasConfigFile('laravel-phones')
            ->hasMigration('create_phones_table')
            ->hasInstallCommand(function(InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToStarRepoOnGitHub('hichxm/laravel-phones');
            });
    }

}