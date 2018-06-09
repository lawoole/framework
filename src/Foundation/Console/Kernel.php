<?php
namespace Lawoole\Foundation\Console;

use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Foundation\Console\Kernel as BaseKernel;
use Lawoole\Console\Application as Artisan;
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
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\SetRequestForConsole::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
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

    /**
     * {@inheritdoc}
     */
    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            $this->artisan = new Artisan($this->app, $this->events, $this->app->version());
            $this->artisan->resolveCommands($this->commands);
        }

        return $this->artisan;
    }
}
