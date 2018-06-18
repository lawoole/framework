<?php
namespace Lawoole\Contracts\Homer;

interface Registrar
{
    /**
     * Register a reference in the Homer.
     *
     * @param array $config
     *
     * @return mixed
     */
    public function resolveReference(array $config);

    /**
     * Register a service in the Homer.
     *
     * @param array $config
     *
     * @return mixed
     */
    public function resolveService(array $config);
}
