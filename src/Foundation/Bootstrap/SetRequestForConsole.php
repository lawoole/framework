<?php
namespace Lawoole\Foundation\Bootstrap;

use Illuminate\Http\Request;
use Lawoole\Contracts\Foundation\Application;

class SetRequestForConsole
{
    /**
     * 设置控制台请求
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function bootstrap(Application $app)
    {
        $request = $this->createConsoleRequest($app);

        $app->instance('console.request', $request);
        $app->instance('request', $request);
    }

    /**
     * 创建控制台请求
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     *
     * @return \Illuminate\Http\Request
     */
    protected function createConsoleRequest(Application $app)
    {
        $uri = $app->make('config')->get('app.url', 'http://localhost');

        $components = parse_url($uri);

        $server = $_SERVER;

        if (isset($components['path'])) {
            $server = array_merge($server, [
                'SCRIPT_FILENAME' => $components['path'],
                'SCRIPT_NAME' => $components['path'],
            ]);
        }

        return Request::create($uri, 'GET', [], [], [], $server);
    }
}
