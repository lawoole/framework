<?php
namespace Lawoole\Server\Commands;

use Illuminate\Console\Command;
use Swoole\Process;

class ReloadCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $signature = 'reload {--task : Reload task worker process only}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Reload the Swoole server';

    /**
     * 执行命令
     */
    public function handle()
    {
        $name = $this->laravel->name();
        $runtimeFile = $this->laravel->storagePath('framework/server.runtime');

        if (file_exists($runtimeFile)) {
            $data = json_decode(file_get_contents($this->laravel->storagePath('framework/server.runtime')), true);

            $this->info("{$name} server is reloading.");

            $signal = $this->option('task') ? SIGUSR2 : SIGUSR1;

            Process::kill($data['pid'], $signal);

            return;
        }

        $this->info("No {$name} server is running.");
    }
}
