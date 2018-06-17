<?php
namespace Lawoole\Support;

class Network
{
    /**
     * Default local host ip.
     */
    const LOCAL_HOST = '127.0.0.1';

    /**
     * Any hosts address.
     */
    const ANY_HOST = '0.0.0.0';

    /**
     * Get the local host ip.
     *
     * @return string
     */
    public static function getLocalHost()
    {
        if ($nets = swoole_get_local_ip()) {
            foreach ($nets as $net => $host) {
                if (self::isUsableHost($host)) {
                    return $host;
                }
            }
        }

        return self::LOCAL_HOST;
    }

    /**
     * Return whether the host is usable.
     *
     * @param string $host
     *
     * @return bool
     */
    protected static function isUsableHost($host)
    {
        return $host && $host != self::LOCAL_HOST && $host != self::ANY_HOST;
    }
}
