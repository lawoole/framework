<?php
namespace Lawoole\Cookie;

use Illuminate\Cookie\CookieJar as BaseCookieJar;

class CookieJar extends BaseCookieJar
{
    /**
     * The default access setting.
     *
     * @var bool
     */
    protected $httpOnly = true;

    /**
     * {@inheritdoc}
     */
    public function make($name, $value, $minutes = 0, $path = null, $domain = null, $secure = null, $httpOnly = null,
        $raw = false, $sameSite = null)
    {
        $httpOnly = $httpOnly ?? $this->httpOnly;

        return parent::make($name, $value, $minutes, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }

    /**
     * Set the default access control for the jar.
     *
     * @param bool $httpOnly
     *
     * @return $this
     */
    public function setDefaultHttpOnly($httpOnly)
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }
}
