<?php
namespace Lawoole\Contracts\Homer;

interface Registrar
{
    /**
     * @param array $reference
     *
     * @return mixed
     */
    public function resolveReference(array $reference);
}