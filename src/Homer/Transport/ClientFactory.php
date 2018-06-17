<?php
namespace Lawoole\Homer\Transport;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Lawoole\Homer\Serialize\Factory as SerializerFactory;
use Lawoole\Homer\Transport\Http\HttpClient;
use Lawoole\Homer\Transport\Whisper\WhisperClient;

class ClientFactory
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The data serializer factory.
     *
     * @var \Lawoole\Homer\Serialize\Factory
     */
    protected $serializerFactory;

    /**
     * All clients.
     *
     * @var \Lawoole\Homer\Transport\Client[]
     */
    protected $clients = [];

    /**
     * Create a client factory instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @param \Lawoole\Homer\Serialize\Factory $serializerFactory
     */
    public function __construct(Application $app, SerializerFactory $serializerFactory)
    {
        $this->app = $app;
        $this->serializerFactory = $serializerFactory;
    }

    /**
     * Get client instance.
     *
     * @param array $config
     *
     * @return \Lawoole\Homer\Transport\Client
     */
    public function client(array $config)
    {
        $key = $this->getClientKey($config);

        if (isset($this->clients[$key])) {
            return $this->clients[$key];
        }

        return $this->clients[$key] = $this->createClient($config);
    }

    /**
     * Create the client instance.
     *
     * @param array $config
     *
     * @return \Lawoole\Homer\Transport\Client
     */
    protected function createClient(array $config)
    {
        $url = Arr::pull($config, 'url');

        $urls = parse_url($url);

        $config['host'] = $urls['host'];
        $config['port'] = $urls['port'];

        switch ($urls['scheme']) {
            case 'http':
                return new HttpClient($this->app, $this->serializerFactory, $config);
            case 'whisper':
                return new WhisperClient($this->app, $this->serializerFactory, $config);
            default:
                throw new InvalidArgumentException('Protocol '.$urls['scheme'].' is not supported for Homer.');
        }
    }

    /**
     * Get the identify key of client.
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
