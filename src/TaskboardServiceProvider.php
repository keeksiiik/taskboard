<?php

namespace dillarionov\Taskboard;

use dillarionov\Taskboard\Http\Livewire\Taskboard;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TaskboardServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('taskboard')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations(['create_statuses_table', 'create_priorities_table', 'create_complexities_table', 'create_tasks_table']);
    }

        public function boot(): void
        {
            parent::boot();
            $this->loadMigrationsFrom(__DIR__.'/database/migrations');
            $this->publishes([
                __DIR__.'/resources/view' => resource_path('views/vendor/dillarionov/taskboard'),
                __DIR__.'/resources/lang' => resource_path('lang/vendor/dillarionov/taskboard'),
            ]);
            Livewire::component('taskboard', Taskboard::class);
        }
}
