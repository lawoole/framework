<?php
namespace Lawoole\Foundation\Console;

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Foundation\Console\Kernel as BaseKernel;
use Symfony\Component\Console\Output\ConsoleOutput;

class Kernel extends BaseKernel implements KernelContract
{
    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected $bootstrappers = [
        \Lawoole\Foundation\Bootstrap\LoadConfigurations::class,
        \Lawoole\Foundation\Bootstrap\RegisterExceptionHandlers::class,
        \Lawoole\Foundation\Bootstrap\RegisterFacades::class,
        \Lawoole\Foundation\Bootstrap\SetRequestForConsole::class,
        \Lawoole\Foundation\Bootstrap\RegisterServiceProviders::class,
        \Lawoole\Foundation\Bootstrap\BootProviders::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected function defineConsoleSchedule()
    {
        $this->schedule($this->app['schedule']);
    }

    /**
     * {@inheritdoc}
     */
    public function handle($input, $output = null)
    {
        $output = $output ?: new ConsoleOutput;

        // Share the input and the output, so that we can get them anywhere easily.
        $this->app->instance('console.input', $input);
        $this->app->instance('console.output', $output);

        parent::handle($input, $output);
    }
}
