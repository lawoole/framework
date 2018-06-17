<?php
namespace Lawoole\Support\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Lawoole\Homer\Context getContext()
 * @method static \Lawoole\Homer\Invocation getInvocation()
 * @method static array getAttachments()
 * @method static mixed getAttachment($key, $default = null)
 */
class Homer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'homer';
    }
}
