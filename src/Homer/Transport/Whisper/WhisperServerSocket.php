<?php
namespace Lawoole\Homer\Transport\Whisper;

use Lawoole\Server\ServerSockets\ServerSocket;

class WhisperServerSocket extends ServerSocket
{
    /**
     * 配置选项
     *
     * @var array
     */
    protected $options = [
        'tcp_fastopen'          => true,
        'open_tcp_nodelay'      => true,
        'open_length_check'     => true,
        'package_length_type'   => 'N',
        'package_max_length'    => 5120000,
        'package_length_offset' => 0,
        'package_body_offset'   => 4
    ];
}