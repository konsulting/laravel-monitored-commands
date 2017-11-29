<?php

namespace Konsulting\Laravel\MonitoredCommands\Tests\Stubs;

use Konsulting\Laravel\MonitoredCommands\MonitoredCommand;

class TestMonitoredCommand extends MonitoredCommand
{
    protected $runsOnce = true;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qce:test-monitored-command {--fail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test a monitored command';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return mixed
     * @throws \Exception
     */
    public function handleCommand()
    {
        if ($this->option('fail')) {
            throw new \Exception('Failed as requested.');
        }
    }
}
