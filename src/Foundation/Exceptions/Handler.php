<?php
namespace Lawoole\Foundation\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Routing\Router;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler implements ExceptionHandlerContract
{
    /**
     * 服务容器
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;

    /**
     * 不需要报告的异常
     *
     * @var array
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * 创建异常处理器
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * 判断异常是否需要报告
     *
     * @param \Exception $e
     *
     * @return bool
     */
    protected function shouldReport(Exception $e)
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }

    /**
     * 报告异常
     *
     * @param \Exception $e
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function report(Exception $e)
    {
        if (!$this->shouldReport($e)) {
            return false;
        }

        if (method_exists($e, 'report')) {
            return $e->report();
        }

        try {
            $logger = $this->container->make(LoggerInterface::class);
        } catch (Exception $ex) {
            throw $e;
        }

        return $logger->error($e->getMessage(), [
            'exception' => $e
        ]);
    }

    /**
     * 渲染异常到 Http 响应
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $e)
    {
        if (method_exists($e, 'render') && $response = $e->render($request)) {
            return Router::toResponse($request, $response);
        } elseif ($e instanceof Responsable) {
            return $e->toResponse($request);
        }

        $e = $this->prepareException($e);

        $fe = FlattenException::create($e);

        $handler = new SymfonyExceptionHandler(config('app.debug', false));

        $decorated = $this->decorate($handler->getContent($fe), $handler->getStylesheet($fe));

        $response = new Response($decorated, $fe->getStatusCode(), $fe->getHeaders());

        $response->exception = $e;

        return $response;
    }

    /**
     * 预处理待渲染的异常
     *
     * @param \Exception $e
     *
     * @return \Exception
     */
    protected function prepareException(Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new AccessDeniedHttpException($e->getMessage(), $e);
        } elseif ($e instanceof TokenMismatchException) {
            $e = new HttpException(419, $e->getMessage(), $e);
        }

        return $e;
    }

    /**
     * 渲染异常页面内容
     *
     * @param string $content
     * @param string $css
     *
     * @return string
     */
    protected function decorate($content, $css)
    {
        return <<<EOF
<!DOCTYPE html>
<html>
    <head>
        <meta name="robots" content="noindex,nofollow" />
        <style>
            /* Copyright (c) 2010, Yahoo! Inc. All rights reserved. Code licensed under the BSD License: http://developer.yahoo.com/yui/license.html */
            html{color:#000;background:#FFF;}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0;}table{border-collapse:collapse;border-spacing:0;}fieldset,img{border:0;}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal;}li{list-style:none;}caption,th{text-align:left;}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;}q:before,q:after{content:'';}abbr,acronym{border:0;font-variant:normal;}sup{vertical-align:text-top;}sub{vertical-align:text-bottom;}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;}input,textarea,select{*font-size:100%;}legend{color:#000;}
            html { background: #eee; padding: 10px }
            img { border: 0; }
            #sf-resetcontent { width:970px; margin:0 auto; }
            $css
        </style>
    </head>
    <body>
        $content
    </body>
</html>
EOF;
    }

    /**
     * 渲染异常到控制台输出
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception $e
     */
    public function renderForConsole($output, Exception $e)
    {
        $console = new ConsoleApplication;

        $console->renderException($e, $output);
    }
}
