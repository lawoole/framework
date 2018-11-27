<?php
namespace Lawoole\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;

class AutoRollBackTransaction
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a transaction guard instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);



        return $response;
    }

    protected function clearTransaction()
    {

    }
}