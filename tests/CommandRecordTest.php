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
}
