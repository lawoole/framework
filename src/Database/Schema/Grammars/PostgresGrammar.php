<?php
namespace Lawoole\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\PostgresGrammar as LaravelPostgresGrammar;
use Illuminate\Support\Fluent;

class PostgresGrammar extends LaravelPostgresGrammar
{
    /**
     * 创建不带时区的 datetime 列定义
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeDateTime(Fluent $column)
    {
        $definition = "timestamp($column->precision) without time zone";

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        return $definition;
    }

    /**
     * 创建带时区的 datetime 列定义
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeDateTimeTz(Fluent $column)
    {
        $definition = "timestamp($column->precision) with time zone";

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        return $definition;
    }

    /**
     * 创建不带时区的 timestamp 列定义
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeTimestamp(Fluent $column)
    {
        $definition = "timestamp($column->precision) without time zone";

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        return $definition;
    }

    /**
     * 创建带时区的 timestamp 列定义
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeTimestampTz(Fluent $column)
    {
        $definition = "timestamp($column->precision) with time zone";

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        return $definition;
    }
}
