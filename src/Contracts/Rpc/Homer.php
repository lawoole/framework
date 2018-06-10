<?php
namespace Lawoole\Contracts\Rpc;

interface Homer
{
    /**
     * Get the context of current invoking.
     *
     * @return \Lawoole\Contracts\Rpc\Context
     */
    public function getContext();
}