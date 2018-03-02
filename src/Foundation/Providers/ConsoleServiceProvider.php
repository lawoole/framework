<?php
namespace Lawoole\Foundation\Providers;

use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\AggregateServiceProvider;

class ConsoleServiceProvider extends AggregateServiceProvider
{
    /**
     * 服务提供者集合
     *
     * @var array
     */
    protected $providers = [
        ArtisanServiceProvider::class,
        ComposerServiceProvider::class,
        MigrationServiceProvider::class,
        ScheduleServiceProvider::class,
    ];

    /**
     * 注册服务
     */
    public function register()
    {
        parent::register();

        $this->registerRequestForConsole();
    }

    /**
     * 注册全局控制器请求
     */
    public function registerRequestForConsole()
    {
        $this->app->singleton('console.request', function ($app) {
            // 控制台需要通过配置定义 Url
            $uri = $app->make('config')->get('app.url', 'http://localhost');

            $components = parse_url($uri);

            $server = $_SERVER;

            if (isset($components['path'])) {
                $server = array_merge($server, [
                    'SCRIPT_FILENAME' => $components['path'],
                    'SCRIPT_NAME'     => $components['path'],
                ]);
            }

            return Request::create($uri, 'GET', [], [], [], $server);
        });
    }
}
