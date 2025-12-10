<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunSmartCageSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'smartcage:run-schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run automatic scheduling for Smart Cage components';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $state = \Illuminate\Support\Facades\DB::table('control_states')->first();

        if (!$state) {
            $this->info('No control state found.');
            return;
        }

        // Only run if Mode is AUTO
        if ($state->mode !== 'AUTO') {
            return;
        }

        $now = now();

        // Check each component
        // Note: Because we might use sleep() for short durations, we should be careful.
        // If multiple components trigger at the exact same minute with short durations, 
        // they will execute sequentially.
        
        $this->checkComponent($state, 'pakan', $now);
        $this->checkComponent($state, 'minum', $now);
        $this->checkComponent($state, 'lampu', $now);

        $this->info('Schedule check completed at ' . $now);
    }

    private function checkComponent($state, $type, $now)
    {
        $scheduleVal = $state->{"{$type}_schedule_val"};
        $scheduleUnit = $state->{"{$type}_schedule_unit"};
        $lastRun = $state->{"last_run_{$type}"} ? \Carbon\Carbon::parse($state->{"last_run_{$type}"}) : null;
        
        // Duration Logic (Convert to Seconds)
        $durationVal = $state->{"{$type}_duration"} ?? 1;
        $durationUnit = $state->{"{$type}_duration_unit"} ?? 'minute';
        $durationInSeconds = ($durationUnit === 'minute') ? $durationVal * 60 : $durationVal;

        // 1. Check if we need to TURN ON
        if ($scheduleVal && $scheduleUnit) {
            $shouldRun = false;
            
            if (!$lastRun) {
                $shouldRun = true;
            } else {
                $nextRun = $lastRun->copy();
                switch ($scheduleUnit) {
                    case 'minute': $nextRun->addMinutes($scheduleVal); break;
                    case 'hour':   $nextRun->addHours($scheduleVal); break;
                    case 'day':    $nextRun->addDays($scheduleVal); break;
                }

                if ($now->greaterThanOrEqualTo($nextRun)) {
                    $shouldRun = true;
                }
            }

            if ($shouldRun) {
                // UPDATE DB: ON
                \Illuminate\Support\Facades\DB::table('control_states')
                    ->where('id', $state->id)
                    ->update([
                        $type => 1,
                        "last_run_{$type}" => $now
                    ]);
                $this->info("Generated ON signal for {$type}");

                // BLOCKING LOGIC FOR SHORT DURATION (< 60s)
                // This ensures precise "Seconds" duration without relying on next minute cron
                if ($durationInSeconds < 60) {
                    $this->info("Short duration detected ({$durationInSeconds}s). Waiting...");
                    sleep($durationInSeconds);
                    
                    \Illuminate\Support\Facades\DB::table('control_states')
                        ->where('id', $state->id)
                        ->update([$type => 0]);
                    
                    $this->info("Auto OFF (Short) for {$type}");
                    return; // Done
                }
            }
        }

        // 2. Check if we need to TURN OFF (Long Duration Logic >= 60s)
        // If logic falls here, it means it's either not running, or running with long duration
        if ($state->$type == 1 && $lastRun && $durationInSeconds >= 60) {
            $turnOffTime = $lastRun->copy()->addSeconds($durationInSeconds);
            if ($now->greaterThanOrEqualTo($turnOffTime)) {
                 \Illuminate\Support\Facades\DB::table('control_states')
                    ->where('id', $state->id)
                    ->update([$type => 0]);
                 $this->info("Auto OFF (Long) for {$type} after {$durationInSeconds} seconds.");
            }
        }
    }
}
