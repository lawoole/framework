<?php
namespace Lawoole\Homer\Transport\Whisper;

use Lawoole\Server\ServerSockets\ServerSocket;

class WhisperServerSocket extends ServerSocket
{
    /**
     * The server socket options.
     *
     * @var array
     */
    protected $options = [
        'tcp_fastopen'          => true,
        'open_tcp_nodelay'      => true,
        'open_length_check'     => true,
        'package_length_type'   => 'N',
        'package_max_length'    => 5120000,
        'package_length_offset' => 4,
        'package_body_offset'   => 8
    ];
}
