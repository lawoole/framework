<?php
namespace Lawoole\Contracts\Foundation;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;

interface ApplicationInterface extends ApplicationContract
{
    /**
     * 获得当前配置的地区
     *
     * @return string
     */
    public function getLocale();

    /**
     * 设置当前地区
     *
     * @param string $locale
     */
    public function setLocale($locale);

    /**
     * 判断当前配置的地区是否为给出的地区
     *
     * @param string $locale
     * @return bool
     */
    public function isLocale($locale);
}
