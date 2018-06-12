<?php
namespace Lawoole\Contracts\Homer;

interface Homer
{
    /**
     * Get the context of current invoking.
     *
     * @return \Lawoole\Contracts\Homer\Context
     */
    public function getContext();
}