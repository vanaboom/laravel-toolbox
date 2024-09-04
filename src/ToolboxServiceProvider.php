<?php

namespace Vanaboom\Toolbox;

use Illuminate\Support\ServiceProvider;

class ToolboxServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/toolbox.php', 'toolbox');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerCommands();
    }

    /**
     * Register package commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\StarterCommand::class,
            ]);
        }
    }
}
