<?php
namespace Lawoole\Server;

use Illuminate\Http\Request;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Routing\RequestManager;
use Lawoole\Swoole\Handlers\HttpServerSocketHandler as HttpServerSocketHandlerContract;

class HttpServerSocketHandler implements HttpServerSocketHandlerContract
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 创建 Http 服务 Socket 处理器
     *
     * @param \Lawoole\Contracts\Foundation\Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * 收到 Http 处理请求时调用
     *
     * @param \Lawoole\Swoole\Server $server
     * @param \Lawoole\Swoole\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest($server, $serverSocket, $request, $response)
    {
        $requestManager = $this->createRequestManager($request, $response);

        // 处理请求
        $requestManager->handle();
    }

    /**
     * 创建请求处理器
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     *
     * @return \Lawoole\Routing\RequestManager
     */
    protected function createRequestManager($request, $response)
    {
        $httpRequest = $this->createHttpRequest($request);
        $httpResponse = $this->createResponseSender($response);

        return new RequestManager($this->app, $httpRequest, $httpResponse);
    }

    /**
     * 创建 Http 请求
     *
     * @param \Swoole\Http\Request $request
     *
     * @return \Illuminate\Http\Request
     */
    protected function createHttpRequest($request)
    {
        $attributes = [
            'swoole_request' => $request
        ];

        return new Request(
            $request->get ?? [],
            $request->post ?? [],
            $attributes,
            $request->cookie ?? [],
            $request->files ?? [],
            $this->getRequestServer($request),
            $request->rawContent()
        );
    }

    /**
     * 获得请求 $_SERVER 形式参数
     *
     * @param \Swoole\Http\Request $request
     *
     * @return array
     */
    protected function getRequestServer($request)
    {
        $server = [];

        // 解析 $request->server
        foreach (($request->server ?? []) as $name => $value) {
            $server[strtoupper($name)] = $value;
        }

        // 解析 $request->header
        foreach (($request->header ?? []) as $name => $value) {
            $server['HTTP_'.strtoupper($name)] = $value;
        }

        return $server;
    }

    /**
     * 创建响应发送器
     *
     * @param \Swoole\Http\Response $response
     *
     * @return \Closure
     */
    protected function createResponseSender($response)
    {
        return function ($httpResponse) use ($response) {
            // Status
            $response->status($httpResponse->getStatusCode());

            // Headers
            foreach ($httpResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
                // 标准化名称
                $name = ucwords($name, '-');

                foreach ($values as $value) {
                    $response->header($name, $value);
                }
            }

            // Cookies
            foreach ($httpResponse->headers->getCookies() as $cookie) {
                $response->cookie(
                    $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(),
                    $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly()
                );
            }

            // Body
            $response->end($httpResponse->getContent());
        };
    }
}
