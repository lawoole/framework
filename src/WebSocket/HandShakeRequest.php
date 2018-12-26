<?php
namespace Lawoole\WebSocket;

use Illuminate\Http\Request;

class HandShakeRequest extends Request
{
    /**
     * Get the WebSocket protocol from the request.
     *
     * @return array|string|null
     */
    public function getWebSocketProtocol()
    {
        return $this->header('Sec-WebSocket-Protocol');
    }

    /**
     * Get the WebSocket key from the request.
     *
     * @return array|string|null
     */
    public function getWebSocketKey()
    {
        return $this->header('Sec-WebSocket-Key');
    }
}
