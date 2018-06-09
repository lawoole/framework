<?php
namespace Lawoole\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar as LaravelPostgresGrammar;
use Illuminate\Support\Fluent;

class PostgresGrammar extends LaravelPostgresGrammar
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

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function typeDateTimeTz(Fluent $column)
    {
        $definition = parent::typeDateTimeTz($column);

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
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

        return $definition;
    }

    /**
     * {@inheritdoc}
     */
    protected function typeTimestampTz(Fluent $column)
    {
        $definition = parent::typeTimestampTz($column);

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        return $definition;
    }
}
