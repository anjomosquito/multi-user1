<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanActivityLog extends Command
{
    protected $signature = 'activitylog:clean {--days=30}';
    protected $description = 'Clean up old activity logs';

    public function handle()
    {
        $days = $this->option('days');
        $date = Carbon::now()->subDays($days);

        $deleted = DB::table('activity_log')
            ->where('created_at', '<', $date)
            ->delete();

        $this->info("Deleted {$deleted} old activity log records.");
    }
}
