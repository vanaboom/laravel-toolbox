<?php

namespace Vanaboom\Toolbox\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'toolbox:starter')]
class StarterCommand extends Command
{
    /**
     * The command's signature.
     *
     * @var string
     */
    public $signature = 'toolbox:starter
                    {--mode= : The environment mode that should be used to serve the application [default: "dev"]}';

    /**
     * The command's description.
     *
     * @var string
     */
    public $description = 'Start serving the application';

    /**
     * Handle the command.
     *
     * @return int
     */
    public function handle()
    {
        $mode = $this->option('mode') ?: config('toolbox.mode');

        return match ($mode) {
            'dev' => $this->startWithServe(),
            'prod' => $this->startWithOctane(),
            default => $this->invalidMode($mode),
        };
    }

    /**
     * Start the with Serve.
     *
     * @return int
     */
    protected function startWithServe()
    {
        return $this->callSilently('serve', [
            '--host' => '0.0.0.0',
            '--port' => '8888',
            '--quiet' => true,
        ]);
    }

    /**
     * Start the server with Octane.
     *
     * @return int
     */
    protected function startWithOctane()
    {
        $args = [
            '--server' => config('octane.server', 'roadrunner'),
            '--host' => '0.0.0.0',
            '--port' => '8888',
            '--rpc-port' => '6001',
            '--https' => config('octane.https'),
        ];

        if (config('toolbox.octan_watch', false)) {
            $args = \Arr::add($args, '--watch', true);
        }

        $args = \Arr::add($args, '--log-level', 'error');
        $args = \Arr::add($args, '--quiet', true);
        $args = \Arr::add($args, '--no-interaction', true);

        return $this->call('octane:start', $args);
    }

    /**
     * Inform the environment type is invalid.
     *
     * @return int
     */
    protected function invalidMode(string $mode)
    {
        $this->error("Invalid Environment Mode: {$mode}.");

        return self::FAILURE;
    }
}
