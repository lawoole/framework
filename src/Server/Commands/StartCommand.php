<?php
namespace Lawoole\Server\Commands;

use Illuminate\Console\Command;

class StartCommand extends Command
{
    /**
     * 命令名
     *
     * @var string
     */
    protected $signature = 'start {--d|daemon= : Run the server in background. }';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Start the Swoole server';

    /**
     * 执行命令
     */
    public function handle()
    {
        $server = $this->laravel->make('server');

        if ($this->option('daemon')) {
            $server->setOptions(['daemon' => true]);
        }

        $this->saveRuntime();

        $server->serve();

        $this->removeRuntime();
    }

    /**
     * 记录服务器运行时信息
     */
    protected function saveRuntime()
    {
        $payload = [
            'name' => $this->laravel->name(),
            'pid'  => getmypid(),
            'time' => time()
        ];

        file_put_contents(
            $this->laravel->storagePath('framework/server.runtime'),
            json_encode($payload, JSON_PRETTY_PRINT)
        );
    }

    /**
     * 移除运行时记录
     */
    protected function removeRuntime()
    {
        $runtime = $this->getRuntimeFilePath();

        @unlink($runtime);
    }

    /**
     * 获得运行时记录文件位置
     *
     * @return string
     */
    protected function getRuntimeFilePath()
    {
        return $this->laravel->storagePath('framework/server.runtime');
    }
}
