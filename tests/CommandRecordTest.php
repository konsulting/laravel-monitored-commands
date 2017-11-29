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
        $started = CommandRecord::create(['name' => 'started_command'])->start();
        $notCompleted = CommandRecord::create(['name' => 'not_completed']);
        $completed = CommandRecord::create(['name' => 'completed_command'])->start()->complete();

        $this->assertFalse($started->isCompleted());
        $this->assertFalse($notCompleted->isCompleted());
        $this->assertTrue($completed->isCompleted());
    }

    /** @test */
    public function it_records_the_time_at_which_the_command_failed()
    {
        $command = CommandRecord::create(['name' => 'failed_command'])
            ->start()->fail();

        $this->assertInstanceOf(Carbon::class, $command->deleted_at);
    }

    /** @test */
    public function it_gets_a_command_record_by_name()
    {
        CommandRecord::create(['name' => 'my_command'])
            ->start()->complete();

        $command = CommandRecord::getByName('my_command')->first();

        $this->assertInstanceOf(CommandRecord::class, $command);
        $this->assertEquals($command->name, 'my_command');
    }

    /** @test */
    public function it_checks_if_a_command_has_been_requested()
    {
        CommandRecord::create(['name' => 'my_command']);
        CommandRecord::create(['name' => 'started_command'])->start();
        CommandRecord::create(['name' => 'completed_command'])->start()->complete();
        CommandRecord::create(['name' => 'failed_command'])->start()->fail();

        $this->assertTrue(CommandRecord::hasBeenRequested('my_command'));
        $this->assertTrue(CommandRecord::hasBeenRequested('started_command'));
        $this->assertTrue(CommandRecord::hasBeenRequested('completed_command'));
        $this->assertTrue(CommandRecord::hasBeenRequested('failed_command'));
    }

    /** @test */
    public function it_checks_how_many_times_a_command_has_been_requested()
    {
        CommandRecord::create(['name' => 'requested_once']);
        CommandRecord::create(['name' => 'requested_twice'])->start()->fail();
        CommandRecord::create(['name' => 'requested_twice'])->start()->complete();

        $this->assertEquals(0, CommandRecord::hasBeenRequestedCount('my_command'));
        $this->assertEquals(1, CommandRecord::hasBeenRequestedCount('requested_once'));
        $this->assertEquals(2, CommandRecord::hasBeenRequestedCount('requested_twice'));
    }

    /** @test */
    public function a_command_is_in_progress_if_it_has_started_but_not_completed_or_failed()
    {
        CommandRecord::create(['name' => 'my_command'])->start()->complete();
        CommandRecord::create(['name' => 'my_command'])->start()->fail();
        CommandRecord::create(['name' => 'my_command'])->start();

        $this->assertTrue(CommandRecord::isInProgress('my_command'));
    }

    /** @test */
    public function a_command_is_not_in_progress_if_it_has_not_started_or_it_has_completed_or_failed()
    {
        CommandRecord::create(['name' => 'my_command'])->start()->complete();
        CommandRecord::create(['name' => 'my_command'])->start()->fail();
        CommandRecord::create(['name' => 'my_command']);

        $this->assertFalse(CommandRecord::isInProgress('my_command'));
    }

    /** @test */
    public function a_command_is_not_in_progress_if_it_has_failed()
    {
        CommandRecord::create(['name' => 'my_command'])->start()->fail();

        $this->assertFalse(CommandRecord::isInProgress('my_command'));
    }

    /** @test */
    public function it_checks_if_a_command_has_completed()
    {
        CommandRecord::create(['name' => 'my_command'])->start()->complete();

        $this->assertTrue(CommandRecord::hasCompleted('my_command'));
    }

    /** @test */
    public function a_command_has_not_completed_if_it_has_failed()
    {
        CommandRecord::create(['name' => 'my_command'])->start()->fail();

        $this->assertFalse(CommandRecord::hasCompleted('my_command'));
    }

    /** @test */
    public function it_retrieves_the_number_of_times_that_a_command_has_completed()
    {
        CommandRecord::create(['name' => 'not_completed'])->start();
        CommandRecord::create(['name' => 'completed_once'])->start()->complete();
        CommandRecord::create(['name' => 'completed_twice'])->start()->complete();
        CommandRecord::create(['name' => 'completed_twice'])->start()->complete();

        $this->assertEquals(0, CommandRecord::hasCompletedCount('not_completed'));
        $this->assertEquals(1, CommandRecord::hasCompletedCount('completed_once'));
        $this->assertEquals(2, CommandRecord::hasCompletedCount('completed_twice'));
    }
}
