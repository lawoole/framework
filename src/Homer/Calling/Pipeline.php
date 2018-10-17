<?php
namespace Lawoole\Homer\Calling;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Pipeline\Pipeline as BasePipeline;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

class Pipeline extends BasePipeline
{
    /**
     * {@inheritdoc}
     */
    protected function prepareDestination(Closure $destination)
    {
        return function ($passable) use ($destination) {
            try {
                return $destination($passable);
            } catch (Exception $e) {
                return $this->handleException($passable, $e);
            } catch (Throwable $e) {
                return $this->handleException($passable, new FatalThrowableError($e));
            }
        };
    }

    /**
     * {@inheritdoc}
     */
    protected function carry()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                try {
                    $slice = parent::carry();

                    $callable = $slice($stack, $pipe);

                    return $callable($passable);
                } catch (Exception $e) {
                    return $this->handleException($passable, $e);
                } catch (Throwable $e) {
                    return $this->handleException($passable, new FatalThrowableError($e));
                }
            };
        };
    }

    /**
     * Handle the given exception.
     *
     * @param mixed $passable
     * @param \Exception $e
     *
     * @return mixed
     */
    protected function handleException($passable, Exception $e)
    {
        $handler = $this->container->make(ExceptionHandler::class);

        $handler->report($e);

        return $this->createExceptionResult($e);
    }

    /**
     * Create an excepted calling result.
     *
     * @param \Throwable $e
     *
     * @return \Lawoole\Homer\Calling\Result
     */
    protected function createExceptionResult($e)
    {
        return new Result(null, $e);
    }
}
