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
}
