<?php
namespace Illuminate\Foundation\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Lawoole\Foundation\Exceptions\MaintenanceModeException;

class CheckForMaintenanceMode
{
    /**
     * 服务容器
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 创建维护检查中间件
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 检查维护状态
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            $data = json_decode(file_get_contents($this->app->storagePath('framework/down.lock')), true);

            throw new MaintenanceModeException($data['time'], $data['retry'], $data['message']);
        }

        return $next($request);
    }
}
