<?php

namespace Konsulting\Laravel\MonitoredCommands;

use Illuminate\Console\Command;

abstract class MonitoredCommand extends Command
{
    /**
     * The command record that relates to the command instance.
     *
     * @var CommandRecord
     */
    protected $record;

    /**
     * True if the command calls itself as part of its execution.
     *
     * @var bool
     */
    protected $isRecursive = false;

    /**
     * If true, the command will not run if it has run before.
     *
     * @var bool
     */
    protected $runsOnce = false;

    /**
     * The command will ONLY run if any of these commands have previously run.
     *
     * @var array
     */
    protected $runsIf = [];

    /**
     * The command will NOT run if any of these commands have previously run.
     *
     * @var array
     */
    protected $doesntRunIf = [];

    /**
     * The maximum number of times that the command can be run. Set to a negative number for no limit.
     *
     * @var int;
     */
    protected $runLimit = -1;

    /**
     * Create the monitored command instance and set the signature.
     */
    public function __construct()
    {
        $this->completeSignature();
        parent::__construct();
    }

    /**
     * Append the --command-record-id option to the command signature.
     *
     * @return void
     */
    protected function completeSignature()
    {
        if (strpos($this->signature, '{--command-record-id}')) {
            return;
        }

        $this->signature = $this->signature . ' {--command-record-id}';
    }

    /**
     * Get the command record that relates to the command. If it has not been set, find or create the record.
     *
     * @return CommandRecord
     */
    public function commandRecord()
    {
        if (isset($this->record)) {
            return $this->record;
        }

        if ($id = $this->option('command-record-id')) {
            $this->record = CommandRecord::find($id);

            return $this->record;
        }

        $this->record = CommandRecord::create([
            'name'      => $this->getName(),
            'arguments' => $this->arguments(),
            'options'   => $this->options(),
            'result'    => '',
        ]);

        return $this->record;
    }

    /**
     * Handle the command execution.
     *
     * @return bool
     */
    public function handle()
    {
        if (! $this->passesChecks()) {
            return false;
        }

        try {
            $this->commandRecord()->start();
            $message = $this->handleCommand();

            if (! $this->isRecursive) {
                return $this->complete($message);
            }
        } catch (\Exception $e) {
            return $this->fail(
                'Error: ' . $e->getMessage()
                . ' at line ' . $e->getLine()
                . ' of ' . $e->getFile()
                . " Stack Trace: " . $e->getTraceAsString()
            );
        }
    }

    /**
     * Perform checks on the command to determine if it should run.
     *
     * @return bool
     */
    protected function passesChecks()
    {
        if ($this->runsOnce && CommandRecord::hasCompleted($this->name)) {
            return $this->fail("Command has run before.");
        }

        if ($this->runsOnce && CommandRecord::isInProgress($this->name)) {
            return $this->fail("Command is running.");
        }

        if ($this->hasRunTooManyTimes()) {
            return $this->fail("Command has been run or requested too many times.");
        }

        foreach ($this->runsIf as $name) {
            if (! CommandRecord::hasCompleted($name)) {
                return $this->fail("Command {$name} has not run yet.");
            }
        }

        foreach ($this->doesntRunIf as $name) {
            if (CommandRecord::hasCompleted($name)) {
                return $this->fail("Command {$name} has already run.");
            }
        }

        if ($this->commandRecord()->hasStarted()) {
            throw new \Exception('The monitored command ' . $this->commandRecord()->id . ' has already started.');
        }

        return true;
    }

    /**
     * Check if the command has been run or requested more times than allowed by the run limit property.
     *
     * @return bool
     */
    protected function hasRunTooManyTimes()
    {
        return $this->runLimit >= 0 &&
            max(CommandRecord::hasBeenRequestedCount($this->name),
                CommandRecord::hasCompletedCount($this->name)) >= $this->runLimit;
    }

    /**
     * Output message and update command record on completion.
     *
     * @param string $message
     * @return bool
     */
    public function complete($message = '')
    {
        $message = $this->stringFromMessage($message);
        $this->output->success($message);
        $this->commandRecord()->complete($message);

        return true;
    }

    /**
     * Output message and update command record on failure.
     *
     * @param string $message
     * @return bool
     */
    protected function fail($message = '')
    {
        $message = $this->stringFromMessage($message);
        $this->output->error($message);
        $this->commandRecord()->fail($message);

        return false;
    }

    /**
     * Get a string representation of the command return value.
     *
     * @param mixed $message
     * @return string
     */
    protected function stringFromMessage($message)
    {
        if (is_string($message)) {
            return $message;
        }

        return var_export($message, true);
    }

    /**
     * The custom monitored command functionality.
     *
     * @return mixed
     */
    abstract protected function handleCommand();
}
