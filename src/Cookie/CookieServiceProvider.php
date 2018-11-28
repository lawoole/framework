<?php
namespace Lawoole\Cookie;

use Illuminate\Cookie\CookieServiceProvider as BaseCookieServiceProvider;

class CookieServiceProvider extends BaseCookieServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->app->singleton('cookie', function ($app) {
            $config = $app->make('config')->get('cookie');

            $jar = new CookieJar;

            $jar->setDefaultPathAndDomain(
                $config['path'], $config['domain'], $config['secure'], $config['same_site'] ?? null
            );

            $jar->setDefaultHttpOnly($config['http_only']);

            return $jar;
        });
    }
}
