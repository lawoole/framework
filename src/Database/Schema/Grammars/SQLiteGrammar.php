<?php
namespace Lawoole\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\SQLiteGrammar as LaravelSQLiteGrammar;
use Illuminate\Support\Fluent;

class SQLiteGrammar extends LaravelSQLiteGrammar
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
}
