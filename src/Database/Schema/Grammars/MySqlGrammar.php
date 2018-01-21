<?php
namespace Lawoole\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\MySqlGrammar as LaravelMySqlGrammar;
use Illuminate\Support\Fluent;

class MySqlGrammar extends LaravelMySqlGrammar
{
    /**
     * 创建 datetime 列定义
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeDateTime(Fluent $column)
    {
        $definition = $column->precision ? "datetime($column->precision)" : 'datetime';

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        if ($column->updateCurrent) {
            $definition = "$definition on update CURRENT_TIMESTAMP";
        }

        return $definition;
    }

    /**
     * 创建 timestamp 列定义
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeTimestamp(Fluent $column)
    {
        $definition = $column->precision ? "timestamp($column->precision)" : 'timestamp';

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        if ($column->updateCurrent) {
            $definition = "$definition on update CURRENT_TIMESTAMP";
        }

        return $definition;
    }
}
