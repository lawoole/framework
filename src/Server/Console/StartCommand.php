<?php
namespace Lawoole\Server\Console;

use Illuminate\Console\Command;

class StartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:start
                            {--d|detach= : Run the server in background}';

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

        if ($this->option('detach')) {
            $server->setOptions(['daemon' => true]);
        }

        $this->saveRuntime();

        $server->start();

        $this->removeRuntime();
    }

    /**
     * Record server runtime info.
     */
    protected function saveRuntime()
    {
        $payload = [
            'name' => $this->laravel->name(),
            'pid'  => getmypid(),
            'time' => time()
        ];

        file_put_contents(
            storage_path('framework/server.runtime'),
            json_encode($payload, JSON_PRETTY_PRINT)
        );
    }

    /**
     * Remove server runtime records.
     */
    protected function removeRuntime()
    {
        $runtime = $this->getRuntimeFilePath();

        @unlink($runtime);
    }

    /**
     * Get the file path which to save server runtime info.
     *
     * @return string
     */
    protected function getRuntimeFilePath()
    {
        return storage_path('framework/server.runtime');
    }
}
