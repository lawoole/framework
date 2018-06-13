<?php
namespace Lawoole\Server\Console;

use Illuminate\Console\Command;

class ServerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server
                            {operation? : The operation to be send}
                            {--d|detach : Run the server in background}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a operation request to the server';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {

    }
}