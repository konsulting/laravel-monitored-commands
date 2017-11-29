<?php

namespace Konsulting\Laravel\MonitoredCommands;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Konsulting\Laravel\EditorStamps\EditorStamps;

class CommandRecord extends Model
{
    use EditorStamps, SoftDeletes;

    /**
     * Allow mass assignment on all properties.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Specify the date type properties.
     *
     * @var array
     */
    protected $dates = [
        'started_at',
        'completed_at',
        'deleted_at',
    ];

    /**
     * Specify casts for properties.
     *
     * @var array
     */
    protected $casts = [
        'options'   => 'json',
        'arguments' => 'json',
    ];

    /**
     * Check if the instance has completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return (bool) $this->completed_at;
    }

    /**
     * Check if the instance has been started.
     *
     * @return bool
     */
    public function hasStarted()
    {
        return isset($this->attributes['started_at']);
    }

    /**
     * Get all command records for the specified name.
     *
     * @param string $name
     * @return Collection
     */
    public static function getByName($name)
    {
        return static::whereName($name);
    }

    /**
     * Check if the named command is in progress.
     *
     * @param string $name
     * @return bool
     */
    public static function isInProgress($name)
    {
        return ! ! static::where('name', $name)
            ->whereNull('completed_at')
            ->whereNotNull('started_at')
            ->first();
    }

    /**
     * Check if the named command has completed.
     *
     * @param string $name
     * @return bool
     */
    public static function hasCompleted($name)
    {
        return ! ! static::where('name', $name)->whereNotNull('completed_at')->first();
    }

    /**
     * Get the number of times that the named command has completed.
     *
     * @param string $name
     * @return int
     */
    public static function hasCompletedCount($name)
    {
        return static::where('name', $name)->whereNotNull('completed_at')->count();
    }

    /**
     * Check if the named command has been requested.
     *
     * @param $name
     * @return bool
     */
    public static function hasBeenRequested($name)
    {
        return ! ! static::where('name', $name)->withTrashed()->first();
    }

    /**
     * Get the number of times that the command has been requested.
     *
     * @param string $name
     * @return int
     */
    public static function hasBeenRequestedCount($name)
    {
        return static::where('name', $name)->withTrashed()->count();
    }

    /**
     * Start the command.
     *
     * @return $this
     */
    public function start()
    {
        if ( ! $this->hasStarted()) {
            $this->update(['started_at' => Carbon::now()]);
        }


        return $this;
    }

    /**
     * Update the completed at time and message upon completion.
     *
     * @param string $message
     * @return $this
     */
    public function complete($message = null)
    {
        $this->update(['completed_at' => Carbon::now(), 'result' => $message ?: '']);

        return $this;
    }

    /**
     * Soft delete the command record and update the message on failure.
     *
     * @param string $message
     * @return $this
     */
    public function fail($message = null)
    {
        $this->update(['deleted_at' => Carbon::now(), 'result' => $message ?: '']);

        return $this;
    }

    /**
     * Get the arguments for the command.
     *
     * @return array
     */
    public function getCommandArguments()
    {
        return array_merge(
            json_decode($this->arguments, true), ['--command-record-id' => $this->getKey()]
        );
    }
}
