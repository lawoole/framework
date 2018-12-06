<?php
namespace Lawoole\Contracts\Server;

interface Factory
{
    /**
     * Register an extension server resolver.
     *
     * @param string $driver
     * @param callable $resolver
     */
    public function extendServer($driver, callable $resolver);

    /**
     * Register an extension server socket resolver.
     *
     * @param string $protocol
     * @param callable $resolver
     */
    public function extendServerSocket($protocol, callable $resolver);

    /**
     * Establish a server based on the configuration.
     *
     * @param array $config
     *
     * @return \Lawoole\Contracts\Server\Server
     */
    public function make(array $config);
}
