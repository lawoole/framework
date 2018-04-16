<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Lawoole\Homer\Rpc\Context getContext()
 * @method static \Lawoole\Homer\Rpc\Invocation getInvocation()
 * @method static array getAttachments()
 * @method static mixed getAttachment($key, $default = null)
 */
class Homer extends Facade
{
    /**
     * 获得容器注册名
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'homer';
    }
}
