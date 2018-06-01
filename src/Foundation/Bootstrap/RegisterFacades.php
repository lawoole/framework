<?php
namespace Lawoole\Foundation\Bootstrap;

use Illuminate\Support\Facades\Facade;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Foundation\AliasLoader;

class RegisterFacades
{
    /**
     * Set the application instance for facade.
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        $this->registerClassAliases($app);
    }

    /**
     * Register all class aliases.
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    protected function registerClassAliases(Application $app)
    {
        $aliases = $app->make('config')->get('app.aliases', []);

        AliasLoader::getInstance($aliases)->register();
    }
}
