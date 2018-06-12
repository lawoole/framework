<?php
namespace Lawoole\Contracts\Snowflake;

interface Snowflake
{
    /**
     * Generate a new snowflake id.
     *
     * @return string
     */
    public function nextId();
}
