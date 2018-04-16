<?php
namespace Lawoole\Homer\Transport;

use Illuminate\Support\Facades\Log;
use Lawoole\Homer\Dispatcher;
use Lawoole\Homer\HomerException;
use Lawoole\Homer\Invocation;
use Throwable;

abstract class Server
{
    /**
     * 调用调度器
     *
     * @var \Lawoole\Homer\Dispatcher
     */
    protected $dispatcher;

    /**
     * 创建服务端
     *
     * @param \Lawoole\Homer\Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * 暴露服务
     */
    public function export()
    {
        try {
            $this->doExport();
        } catch (Throwable $e) {
            Log::channel('homer')->warning('Export server failed, cause: '.$e->getMessage(), [
                'exception' => $e
            ]);

            throw new HomerException('Export server failed, cause: '.$e->getMessage().'.');
        }
    }

    /**
     * 暴露服务
     */
    abstract protected function doExport();

    /**
     * 处理消息
     *
     * @param mixed $message
     *
     * @return mixed
     */
    public function handleMessage($message)
    {
        try {
            if (!$message instanceof Invocation) {
                return $message;
            }

            $invoker = $this->dispatcher->dispatch($message);

            return $invoker->invoke($message);
        } catch (HomerException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::channel('homer')->warning($e->getMessage(), [
                'exception' => $e
            ]);

            throw $e;
        }
    }
}