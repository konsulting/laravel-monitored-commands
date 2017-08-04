<?php

namespace Konsulting\Laravel\MonitoredCommands;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class MonitoredCommandsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Queue::failing(function (JobFailed $event) {
            if ($event->job instanceof MonitoredCommand) {
                $event->job->commandRecord()->fail($event->exception->getMessage());
            }
        });
    }

    public function register()
    {
        //
    }
}
