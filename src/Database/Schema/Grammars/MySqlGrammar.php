<?php
namespace Lawoole\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as LaravelMySqlGrammar;
use Illuminate\Support\Fluent;

class MySqlGrammar extends LaravelMySqlGrammar
{
    /**
     * {@inheritdoc}
     */
    protected function typeDateTime(Fluent $column)
    {
        $definition = parent::typeDateTime($column);

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        if ($column->updateCurrent) {
            $definition = "$definition on update CURRENT_TIMESTAMP";
        }

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function typeTimestamp(Fluent $column)
    {
        $definition = parent::typeTimestamp($column);

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        if ($column->updateCurrent) {
            $definition = "$definition on update CURRENT_TIMESTAMP";
        }

        return $definition;
    }
}
