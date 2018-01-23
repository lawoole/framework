<?php
namespace Lawoole\Events;

class LocaleUpdated
{
    /**
     * 更新后的地区
     *
     * @var string
     */
    public $locale;

    /**
     * 创建地区更新事件
     *
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}
