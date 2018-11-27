<?php
namespace Lawoole\Server\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;

class StartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:start
                            {--f|filename= : The file to save runtime info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start the server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $server = $this->laravel->make('server');

        $this->line("{$this->laravel->name()} server is starting...");

        $this->processRuntime($server);

        $server->start();
    }

    /**
     * Process the server runtime info.
     *
     * @param \Lawoole\Contracts\Server\Server $server
     */
    protected function processRuntime($server)
    {
        $filename = $this->option('filename') ?? storage_path('framework/server.runtime');

        $server->registerEventCallback('Launching', function () use ($filename) {
            $this->saveRuntime($filename);
        });

        $server->registerEventCallback('Start', function () {
            $this->info("{$this->laravel->name()} server is running.");
        });

        $server->registerEventCallback('Shutdown', function () use ($filename) {
            $this->removeRuntime($filename);

            $this->info("{$this->laravel->name()} server is stopped.");
        });
    }

    /**
     * Record server runtime info.
     *
     * @param string $filename
     */
    protected function saveRuntime($filename)
    {
        $payload = [
            'name' => $this->laravel->name(),
            'pid'  => getmypid(),
            'time' => (string) Carbon::now(),
        ];

        file_put_contents($filename, json_encode($payload, JSON_PRETTY_PRINT));
    }

    /**
     * Remove server runtime records.
     *
     * @param string $filename
     */
    protected function removeRuntime($filename)
    {
        @unlink($filename);
    }
}
