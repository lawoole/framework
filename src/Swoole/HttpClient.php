<?php
namespace Lawoole\Swoole;

use BadMethodCallException;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Swoole\Http\Client as SwooleHttpClient;

class HttpClient extends Client
{
    /**
     * Swoole 客户端对象
     *
     * @var \Swoole\Http\Client
     */
    protected $swooleClient;

    /**
     * 请求路径
     *
     * @var string
     */
    protected $path;

    /**
     * 查询参数
     *
     * @var array
     */
    protected $queries;

    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverEvents = [
        'Connect', 'Error', 'Close'
    ];

    /**
     * 创建客户端
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // 支持通过 Url 解析
        if ($url = Arr::get($config, 'url')) {
            $components = parse_url($url);

            if (empty($config['host'])) {
                $config['host'] = Arr::get($components, 'host', '127.0.0.1');
            }

            if (empty($config['port'])) {
                $config['port'] = Arr::get($components, 'port', 80);
            }

            if (empty($config['ssl'])) {
                $config['ssl'] = strtolower(Arr::get($components, 'scheme', 'http')) == 'https';
            }
        }

        parent::__construct($config);
    }

    /**
     * 创建 Swoole 客户端
     *
     * @return \Swoole\Http\Client
     */
    public function createSwooleClient()
    {
        // Http 客户端只支持异步
        if ($this->synchronized) {
            throw new InvalidArgumentException('Http client cannot be synchronized');
        }

        return new SwooleHttpClient($this->host, $this->port, $this->ssl);
    }

    /**
     * 连接服务端
     */
    public function connect()
    {
        throw new BadMethodCallException('Method is useless for this client');
    }

    /**
     * 设置请求方法
     *
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $method = strtoupper($method);

        $this->swooleClient->setMethod($method);

        return $this;
    }

    /**
     * 设置请求路径
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * 设置查询参数
     *
     * @param array $queries
     *
     * @return $this
     */
    public function setQueries($queries)
    {
        $this->queries = $queries;

        return $this;
    }

    /**
     * 设置请求头
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers)
    {
        $names = array_map(function ($name) {
            return implode('-', array_map('ucfirst', explode('-', $name)));
        }, array_keys($headers));

        $headers = array_column($names, $headers);

        $this->swooleClient->setHeaders($headers);

        return $this;
    }

    /**
     * 设置内容类型
     *
     * @param string $contentType
     *
     * @return $this
     */
    protected function setContentType($contentType)
    {
        $headers = $this->swooleClient->requestHeaders;

        $headers['Content-Type'] = $contentType;

        $this->setHeaders($headers);

        return $this;
    }

    /**
     * 设置请求体
     *
     * @param string $body
     * @param string $contentType
     *
     * @return $this
     */
    public function setBody($body, $contentType = null)
    {
        $this->swooleClient->setData($body);

        if ($contentType) {
            $this->setContentType($contentType);
        }

        return $this;
    }

    /**
     * 设置表单参数
     *
     * @param array $formParams
     *
     * @return $this
     */
    public function setFormParams($formParams)
    {
        $body = $formParams ? http_build_query($formParams) : '';

        $this->setBody($body, 'application/x-www-form-urlencoded');

        return $this;
    }

    /**
     * 发送请求
     *
     * @param string $path
     * @param array $queries
     *
     * @return bool
     */
    public function execute($path = null, $queries = null)
    {
        $path = $path !== null ? $path : $this->path;
        $queries = $queries !== null ? $queries : $this->queries;

        if ($queries) {
            $path = $path.'?'.http_build_query($queries);
        }

        return $this->swooleClient->execute($path, function () {
            $this->dispatchEvent('Response', $this);
        });
    }
}
