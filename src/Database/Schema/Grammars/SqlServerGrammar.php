<?php
namespace Lawoole\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\SqlServerGrammar as LaravelSqlServerGrammar;
use Illuminate\Support\Fluent;

class SqlServerGrammar extends LaravelSqlServerGrammar
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
        $definition = $column->precision ? "datetime2($column->precision)" : 'datetime';

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
        $definition = $column->precision ? "datetimeoffset($column->precision)" : 'datetimeoffset';

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
        $definition = $column->precision ? "datetime2($column->precision)" : 'datetime';

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        return $definition;
    }

    /**
     * 创建带时区的 timestamp 列定义
     *
     * @link https://msdn.microsoft.com/en-us/library/bb630289(v=sql.120).aspx
     *
     * @param \Illuminate\Support\Fluent $column
     *
     * @return string
     */
    protected function typeTimestampTz(Fluent $column)
    {
        $definition = $column->precision ? "datetimeoffset($column->precision)" : 'datetimeoffset';

        if ($column->useCurrent) {
            $definition = "$definition default CURRENT_TIMESTAMP";
        }

        return $definition;
    }
}
