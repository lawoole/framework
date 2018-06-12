<?php
namespace Lawoole\Contracts\Server;

interface Factory
{
    /**
     * Establish a server based on the configuration.
     *
     * @param array $config
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function make(array $config);
}