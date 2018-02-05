<?php
namespace Lawoole\Swoole;

use InvalidArgumentException;

class WebSocketClient extends HttpClient
{
    /**
     * 可用事件回调
     *
     * @var array
     */
    protected $serverEvents = [
        'Connect', 'Error', 'Message', 'Close'
    ];

    /**
     * 创建 Swoole 客户端
     *
     * @return \Swoole\Http\Client
     */
    public function createSwooleClient()
    {
        // WebSocket 客户端只支持异步
        if ($this->synchronized) {
            throw new InvalidArgumentException('WebSocket client cannot be synchronized');
        }

        return parent::createSwooleClient();
    }

    /**
     * 进行 WebSocket 握手
     *
     * @param string $path
     *
     * @return bool
     */
    public function upgrade($path)
    {
        return $this->swooleClient->upgrade($path, function () {
            $this->dispatchEvent('Upgrade', $this);
        });
    }

    /**
     * 推送 WebSocket 消息
     *
     * @param string $data
     * @param int $opCode
     * @param bool $finish
     *
     * @return bool
     */
    public function push($data, $opCode = WEBSOCKET_OPCODE_TEXT, $finish = false)
    {
        return $this->swooleClient->push($data, $opCode, $finish);
    }

    /**
     * 注册事件回调
     */
    protected function registerMessageCallback()
    {
        $this->swooleClient->on('Message', function ($client, $frame) {
            $this->dispatchEvent('Message', $this, $frame);
        });
    }
}
