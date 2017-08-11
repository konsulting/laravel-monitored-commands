<?php

namespace Konsulting\Laravel\MonitoredCommands\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Konsulting\Laravel\MonitoredCommands\CommandRecord;

class CommandRecordTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_has_a_name()
    {
        $record = CommandRecord::create(['name' => 'my_record']);

        $this->assertEquals('my_record', $record->name);
    }

    /** @test */
    public function it_records_the_time_at_which_the_command_was_started()
    {
        $record = CommandRecord::create(['name' => 'my_record']);
        $record->start();

        $this->assertInstanceOf(Carbon::class, $record->started_at);
    }

    /** @test */
    public function it_checks_if_the_command_has_started()
    {
        $started = CommandRecord::create(['name' => 'started_command']);
        $notStarted = CommandRecord::create(['name' => 'not_started_command']);
        $started->start();

        $this->assertTrue($started->hasStarted());
        $this->assertFalse($notStarted->hasStarted());
    }

    /** @test */
    public function it_checks_if_the_command_has_completed()
    {
        $started = CommandRecord::create(['name' => 'started_command']);
        $started->start();
        $notCompleted = CommandRecord::create(['name' => 'not_completed']);
        $completed = CommandRecord::create(['name' => 'completed_command']);
        $completed->start()->complete();

        $this->assertFalse($started->isCompleted());
        $this->assertFalse($notCompleted->isCompleted());
        $this->assertTrue($completed->isCompleted());
    }
}
