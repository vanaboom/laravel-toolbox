<?php

namespace Vanaboom\Toolbox;

use Illuminate\Support\ServiceProvider;
use Vanaboom\Toolbox\Commands\StarterCommand;
use Vanaboom\Toolbox\Commands\PublishDockerCommand;
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
        $this->publishes([
            __DIR__.'/../config/toolbox.php' => config_path('toolbox.php'),
        ], 'toolbox-config');

        // publish docker scaffold
        $this->publishes([
            __DIR__.'/../stubs/docker' => base_path('.docker'),
        ], 'toolbox-docker');

        if ($this->app->runningInConsole()) {
            $this->commands([
                StarterCommand::class,
                PublishDockerCommand::class,
            ]);
        }
    }

}
