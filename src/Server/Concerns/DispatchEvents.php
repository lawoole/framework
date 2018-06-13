<?php
namespace Lawoole\Server\Concerns;

use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

trait DispatchEvents
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The event handler.
     *
     * @var mixed
     */
    protected $handler;

    /**
     * The output for console.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * Dispatch the event to the handler.
     *
     * @param string $event
     * @param array $arguments
     */
    public function dispatchEvent($event, ...$arguments)
    {
        if ($this->handler == null) {
            return;
        }

        try {
            if (method_exists($this->handler, $method = "on{$event}")) {
                $this->handler->$method(...$arguments);
            }
        } catch (Exception $e) {
            $this->handleException($e);
        } catch (Throwable $e) {
            $this->handleException(new FatalThrowableError($e));
        }
    }

    /**
     * Handle the exception with the exception handler.
     *
     * @param \Exception $e
     */
    protected function handleException(Exception $e)
    {
        $handler = $this->app->make(ExceptionHandler::class);

        $handler->report($e);

        $handler->renderForConsole($this->output, $e);
    }
}
