<?php
namespace Lawoole\Server\Console;

use Illuminate\Console\Command;
use Swoole\Process;

class ShutdownCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:shutdown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop the server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $runtimeFile = storage_path('framework/server.runtime');

        if (!file_exists($runtimeFile)) {
            return $this->info("{$this->laravel->name()} server is not running.");
        }

        $payload = json_decode(file_get_contents(storage_path('framework/server.runtime')), true);

        Process::kill($payload['pid'], SIGTERM);

        $this->info("{$this->laravel->name()} server is shutting down.");
    }
}
