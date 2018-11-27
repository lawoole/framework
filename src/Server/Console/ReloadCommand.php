<?php
namespace Lawoole\Server\Console;

use Illuminate\Console\Command;
use Swoole\Process;

class ReloadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:reload
                            {--task : Reload task worker processes only}
                            {--f|filename= : The file to save runtime info}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reload the server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payload = $this->getServerRuntime();

        if (empty($payload)) {
            return $this->info("{$this->laravel->name()} server is not running.");
        }

        Process::kill($payload['pid'], $this->option('task') ? SIGUSR2 : SIGUSR1);

        $this->info("{$this->laravel->name()} server is going to reload...");
    }

    /**
     * Get the running info.
     *
     * @return array
     */
    protected function getServerRuntime()
    {
        $filename = $this->option('filename') ?? storage_path('framework/server.runtime');

        if (! file_exists($filename)) {
            return null;
        }

        return json_decode(file_get_contents($filename), true);
    }
}
