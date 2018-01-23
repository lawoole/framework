<?php
namespace Lawoole\Support;

interface TransferableInterface
{
    /**
     * 获得来源工作进程 Id
     *
     * @return int
     */
    public function getSrcWorkerId();
}
