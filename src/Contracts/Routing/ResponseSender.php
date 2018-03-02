<?php
namespace Lawoole\Contracts\Routing;

use Symfony\Component\HttpFoundation\Response;

interface ResponseSender
{
    /**
     * 发送响应
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function header(Response $response);
}
