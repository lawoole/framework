<?php
namespace Lawoole\Database\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\SQLiteGrammar as LaravelSQLiteGrammar;
use Illuminate\Support\Fluent;

class SQLiteGrammar extends LaravelSQLiteGrammar
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
        return $column->useCurrent ? 'datetime default CURRENT_TIMESTAMP' : 'datetime';
    }
}
