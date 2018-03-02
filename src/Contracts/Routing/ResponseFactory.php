<?php
namespace Lawoole\Contracts\Routing;

use Illuminate\Contracts\Routing\ResponseFactory as FactoryContract;

interface ResponseFactory extends FactoryContract
{
    /**
     * 创建延迟响应
     *
     * @param \Lawoole\Routing\RequestManager $requestManager
     * @param int $timeout
     *
     * @return \Lawoole\Http\FutureResponse
     */
    public function future($requestManager = null, $timeout = 0);

    /**
     * 创建分块响应
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @param int $step
     *
     * @return \Lawoole\Http\ChunkResponse
     */
    public function chunk($content = '', $status = 200, array $headers = [], $step = null);
}
