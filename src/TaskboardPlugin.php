<?php

namespace dillarionov\Taskboard;

use dillarionov\Taskboard\Filament\Pages\TaskboardPage;
use Filament\Contracts\Plugin;
use Filament\Panel;

class TaskboardPlugin implements Plugin
{

    public function getId(): string
    {
        return 'dillarionov-taskboard';
    }

    public function register(Panel $panel): void
    {
        $panel->pages(
            [
                TaskboardPage::class,
            ]
        );
    }

    public function boot(Panel $panel): void
    {

    }
}
