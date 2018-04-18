<?php
namespace Lawoole\Homer\Transport;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lawoole\Homer\Transport\Http\HttpClient;
use Lawoole\Homer\Transport\Whisper\WhisperClient;

class ClientFactory
{
    /**
     * 已创建的客户端
     *
     * @var \Lawoole\Homer\Transport\Client[]
     */
    protected $clients = [];

    /**
     * 获得客户端
     *
     * @param array $config
     *
     * @return \Lawoole\Homer\Transport\Client
     */
    public function getClient(array $config)
    {
        $key = $this->getClientKey($config);

        if (isset($this->clients[$key])) {
            return $this->clients[$key];
        }

        return $this->clients[$key] = $this->createClient($config);
    }

    /**
     * 创建客户端
     *
     * @param array $config
     *
     * @return \Lawoole\Homer\Transport\Client
     */
    protected function createClient(array $config)
    {
        $url = Arr::pull($config, 'url');

        $urls = parse_url($url);

        switch ($urls['scheme']) {
            case 'http':
                return new HttpClient($urls['host'], $urls['port'], $config);
            case 'whisper':
                return new WhisperClient($urls['host'], $urls['port'], $config);
            default:
                throw new InvalidArgumentException('Protocol '.$urls['scheme'].' is not supported for Homer.');
        }
    }

    /**
     * 获得客户端标记
     *
     * @param array $config
     *
     * @return string
     */
    protected function getClientKey(array $config)
    {
        $url = Arr::pull($config, 'url');

        $parameters = array_map(function ($value) {
            return is_bool($value) ? var_export($value, true) : (string) $value;
        }, $config);

        ksort($parameters);

        return $url.'?'.http_build_query($parameters);
    }
}
