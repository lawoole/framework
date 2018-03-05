<?php
namespace Lawoole\Http;

use Illuminate\Http\Request;
use Lawoole\Contracts\Foundation\Application;
use Lawoole\Routing\RequestManager;
use Lawoole\Server\ServerSockets\HttpServerSocketHandler as BaseHttpServerSocketHandler;

class HttpServerSocketHandler extends BaseHttpServerSocketHandler
{
    /**
     * 服务容器
     *
     * @var \Lawoole\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * 创建 Http 服务事件处理器
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
     * @param \Lawoole\Server\Server $server
     * @param \Lawoole\Server\ServerSockets\ServerSocket $serverSocket
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest($server, $serverSocket, $request, $response)
    {
        $this->createRequestManager($request, $response)->handle();
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
            static::sendHeaders($response, $httpResponse);
            static::sendContent($response, $httpResponse);

            if ($httpResponse instanceof MultiBulkResponse && !$httpResponse->isStep(MultiBulkResponse::STEP_FINISH)) {
                return;
            }

            $response->end();
        };
    }

    /**
     * 发送响应头
     *
     * @param \Swoole\Http\Response $response
     * @param \Symfony\Component\HttpFoundation\Response $httpResponse
     */
    protected static function sendHeaders($response, $httpResponse)
    {
        if ($response->header !== null) {
            // 已经设置过响应头，就认为是已经发送过响应头
            return;
        }

        if ($httpResponse instanceof MultiBulkResponse && !$httpResponse->isStep(MultiBulkResponse::STEP_HEADER)) {
            return;
        }

        $response->status($httpResponse->getStatusCode());

        /* RFC2616 - 14.18 says all Responses need to have a Date */
        if (!$httpResponse->headers->has('Date')) {
            $httpResponse->setDate(\DateTime::createFromFormat('U', time()));
        }

        foreach ($httpResponse->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            // 标准化名称
            $name = ucwords($name, '-');

            foreach ($values as $value) {
                $response->header($name, $value);
            }
        }

        foreach ($httpResponse->headers->getCookies() as $cookie) {
            if ($cookie->isRaw()) {
                $response->cookie(
                    $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(),
                    $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly()
                );
            } else {
                $response->rawcookie(
                    $cookie->getName(), $cookie->getValue(), $cookie->getExpiresTime(), $cookie->getPath(),
                    $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly()
                );
            }
        }
    }

    /**
     * 发送响应体
     *
     * @param \Swoole\Http\Response $response
     * @param \Symfony\Component\HttpFoundation\Response $httpResponse
     */
    protected static function sendContent($response, $httpResponse)
    {
        $response->write($httpResponse->getContent());
    }
}
