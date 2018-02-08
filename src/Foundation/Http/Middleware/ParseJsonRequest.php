<?php
namespace Lawoole\Foundation\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ParseJsonRequest
{
    /**
     * 解析 Json 请求参数
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param bool $asObject
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next, $asObject = false)
    {
        $contentType = $request->header('Content-Type');

        if (Str::startsWith($contentType, 'application/json')) {
            // 解析 Json 字符串
            $parameters = json_decode($request->getContent(), !$asObject);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidArgumentException('Json string cannot decoded, error: '.json_last_error_msg());
            }

            $request->request->replace($parameters);
        }

        return $next($request);
    }
}
