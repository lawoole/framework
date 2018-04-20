<?php
namespace Lawoole\Homer;

use InvalidArgumentException;

class Url
{
    /**
     * 协议
     *
     * @var string
     */
    protected $scheme;

    /**
     * 鉴权用户
     *
     * @var string
     */
    protected $username;

    /**
     * 鉴权密码
     *
     * @var string
     */
    protected $password;

    /**
     * 主机名
     *
     * @var string
     */
    protected $host;

    /**
     * 端口
     *
     * @var int
     */
    protected $port;

    /**
     * 路径
     *
     * @var string
     */
    protected $path;

    /**
     * 参数
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * 字符串表示
     *
     * @var string
     */
    protected $string;

    /**
     * 创建 Url 对象
     *
     * @param string $scheme
     * @param string $host
     * @param int $port
     * @param string $path
     * @param array $parameters
     * @param string $username
     * @param string $password
     */
    public function __construct($scheme = null, $host = null, $port = 0, $path = null, array $parameters = [],
        $username = null, $password = null)
    {
        if (empty($username) && $password) {
            throw new InvalidArgumentException("Cannot define an url's password without username.");
        }

        $this->scheme = $scheme;
        $this->username = $username;
        $this->password = $password;

        $this->host = $host;
        $this->port = $port && $port > 0 ? $port : 0;

        $this->path = ltrim($path, '/');

        $this->parameters = $parameters;
    }

    /**
     * 解析 Url 字符串获得对象
     *
     * @param string $url
     *
     * @return \Lawoole\Homer\Url
     */
    public static function valueOf($url)
    {
        $components = parse_url($url);

        $scheme = $components['scheme'] ?? null;
        $username = $components['username'] ?? null;
        $password = $components['password'] ?? null;

        $host = $components['host'] ?? null;
        $port = $components['port'] ?? 0;

        $path = $components['path'] ?? null;

        if (isset($components['query'])) {
            parse_str($components['query'], $parameters);
        } else {
            $parameters = [];
        }

        return new self($scheme, $host, $port, $path, $parameters, $username, $password);
    }

    /**
     * 获得协议
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * 获得鉴权用户
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * 获得鉴权密码
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * 获得主机名
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * 获得端口
     *
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * 获得端口地址
     *
     * @return string
     */
    public function getSocketAddress()
    {
        return $this->host.($this->port ? '.'.$this->port : '');
    }

    /**
     * 获得路径
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 获得参数
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * 判断参数是否存在
     *
     * @param string $key
     *
     * @return bool
     */
    public function hasParameter($key)
    {
        return isset($this->parameters[$key]) || array_key_exists($key, $this->parameters);
    }

    /**
     * 获得参数
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParameter($key, $default = null)
    {
        if (isset($this->parameters[$key]) || array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * 获得参数
     *
     * @param string $key
     * @param int $default
     *
     * @return int
     */
    public function getIntParameter($key, $default = 0)
    {
        $value = $this->getParameter($key, $this);

        if ($value !== $this) {
            return (int) $value;
        }

        return $default;
    }

    /**
     * 获得参数
     *
     * @param string $key
     * @param bool $default
     *
     * @return bool
     */
    public function getBoolParameter($key, $default = false)
    {
        $value = $this->getParameter($key, $this);

        if ($value !== $this) {
            return in_array($value, ['true', 'yes', 'on', '1']);
        }

        return $default;
    }

    /**
     * 获得服务识别串
     *
     * @return string
     */
    public function getServerIdentify()
    {
        return $this->getPath().':'.$this->getPort();
    }

    /**
     * 构建字符串表示
     *
     * @param bool $withAuth
     * @param bool $withParameters
     *
     * @return string
     */
    protected function buildString($withAuth = false, $withParameters = true)
    {
        $url = $this->scheme ? $this->scheme.'://' : '';

        if ($withAuth && $this->username) {
            $url .= $this->username.($this->password ? ':'.$this->password.'@' : '@');
        }

        if ($this->host) {
            $url .= $this->host.($this->port ? ':'.$this->port : '');
        }

        $url .= '/'.$this->path;

        if ($withParameters && $this->parameters) {
            $parameters = array_map(function ($value) {
                return is_bool($value) ? var_export($value, true) : (string) $value;
            }, $this->parameters);

            $url .= '?'.http_build_query($parameters);
        }

        return $url;
    }

    /**
     * 转为字符串
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->string === null) {
            $this->string = $this->buildString(false, true);
        }

        return $this->string;
    }
}
