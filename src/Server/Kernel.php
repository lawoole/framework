<?php
namespace Lawoole\Server;

use Lawoole\Console\Kernel as ConsoleKernel;
use Lawoole\Contracts\Server\Kernel as KernelContract;

class Kernel extends ConsoleKernel implements KernelContract
{
    /**
     * Swoole 服务命令集合
     *
     * @var array
     */
    protected $swooleServerCommands = [
        'command.start',
        'command.shutdown',
    ];

    /**
     * 获得可用的命令
     *
     * @return array
     */
    protected function getUsableCommands()
    {
        return $this->swooleServerCommands;
    }
}
