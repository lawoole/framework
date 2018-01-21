<?php
namespace Lawoole\Swoole;

use Lawoole\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Swoole 服务命令集合
     *
     * @var array
     */
    protected $swooleServerCommands = [
        'command.swoole.run',
        'command.swoole.stop',
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
