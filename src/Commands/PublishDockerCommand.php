<?php
namespace Vanaboom\Toolbox\Commands;

use Illuminate\Console\Command;

class PublishDockerCommand extends Command
{
    protected $signature = 'toolbox:publish-docker {--force : Overwrite any existing files}';
    protected $description = 'Publish the .docker scaffold (Dockerfile, Supervisor, configs) into the application root';

    public function handle(): int
    {
        $params = [
            '--provider' => 'Vanaboom\\Toolbox\\ToolboxServiceProvider',
            '--tag'      => 'toolbox-docker',
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
        $this->components->info('✔ .docker scaffold published.');

        return self::SUCCESS;
    }
}