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
        $name = $this->laravel->name();
        $runtimeFile = storage_path('framework/server.runtime');

        if (file_exists($runtimeFile)) {
            $data = json_decode(file_get_contents(storage_path('framework/server.runtime')), true);

            $this->info("{$name} server is reloading.");

            $signal = $this->option('task') ? SIGUSR2 : SIGUSR1;

            Process::kill($data['pid'], $signal);

            return;
        }

        $this->info("No {$name} server is running.");
    }
}
