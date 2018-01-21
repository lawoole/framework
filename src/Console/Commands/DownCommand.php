<?php
namespace Lawoole\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\InteractsWithTime;

class DownCommand extends Command
{
    use InteractsWithTime;

    /**
     * 命令名
     *
     * @var string
     */
    protected $signature = 'down {--message= : The message for the maintenance mode. }
                                     {--retry= : The number of seconds after which the request may be retried.}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Put the application into maintenance mode';

    /**
     * 执行命令
     */
    public function handle()
    {
        file_put_contents(
            $this->laravel->storagePath('framework/down.lock'),
            json_encode($this->getDownFilePayload(), JSON_PRETTY_PRINT)
        );

        $this->comment('Application is now in maintenance mode.');
    }

    /**
     * 获得维护文件信息
     *
     * @return array
     */
    protected function getDownFilePayload()
    {
        return [
            'time'    => $this->currentTime(),
            'message' => $this->option('message'),
            'retry'   => $this->getRetryTime(),
        ];
    }

    /**
     * 获得客户端重试时间
     *
     * @return int|null
     */
    protected function getRetryTime()
    {
        $retry = $this->option('retry');

        return is_numeric($retry) && $retry > 0 ? (int) $retry : null;
    }
}
