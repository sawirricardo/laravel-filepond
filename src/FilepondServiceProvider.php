<?php

namespace Sawirricardo\LaravelFilepond;

use Illuminate\Support\Facades\Route;
use Sawirricardo\LaravelFilepond\Commands\FilepondCleanCommand;
use Sawirricardo\LaravelFilepond\Http\Controllers\LaravelFilepondController;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilepondServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-filepond')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(FilepondCleanCommand::class);
    }

    public function bootingPackage()
    {
        Route::prefix(config('filepond.server_url'))
            ->name('filepond.')
            ->middleware(config('filepond.middleware'))
            ->group(function () {
                Route::get('/', [LaravelFilepondController::class, 'head'])->name('head');
                Route::patch('/', [LaravelFilepondController::class, 'update'])->name('update');
                Route::post('/', [LaravelFilepondController::class, 'store'])->name('store');
                Route::delete('/', [LaravelFilepondController::class, 'destroy'])->name('destroy');
            });
    }
}
