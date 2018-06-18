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
                            {--task : Reload task worker processes only}';

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
        $runtimeFile = storage_path('framework/server.runtime');

        if (file_exists($runtimeFile)) {
            return $this->info("No {$this->laravel->name()} server is running.");
        }

        $data = json_decode(file_get_contents(storage_path('framework/server.runtime')), true);

        $signal = $this->option('task') ? SIGUSR2 : SIGUSR1;

        Process::kill($data['pid'], $signal);

        $this->info("{$this->laravel->name()} server is going to reload.");
    }
}
