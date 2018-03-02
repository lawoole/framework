<?php
namespace Lawoole\Routing;

use Illuminate\Routing\ResponseFactory as BaseResponseFactory;
use Lawoole\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class ResponseFactory extends BaseResponseFactory implements ResponseFactoryContract
{
    /**
     * 创建延迟响应
     *
     * @param \Lawoole\Routing\RequestManager $requestManager
     * @param int $timeout
     *
     * @return \Lawoole\Routing\FutureResponse
     */
    public function future($requestManager = null, $timeout = 0)
    {

    }

    /**
     * 创建多步响应
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @param int $step
     *
     * @return \Lawoole\Routing\MultiBulkResponse
     */
    public function bulk($content = '', $status = 200, array $headers = [], $step = null)
    {
        // TODO: Implement bulk() method.
    }
}
