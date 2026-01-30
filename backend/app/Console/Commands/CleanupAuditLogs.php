<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class CleanupAuditLogs extends Command
{
    protected $signature = 'audit:cleanup {days=90 : Number of days to retain logs}';
    protected $description = 'Delete old audit logs older than specified days';

    public function handle()
    {
        $days = $this->argument('days');
        
        $count = AuditLog::where('created_at', '<', now()->subDays($days))->count();
        
        if ($count === 0) {
            $this->info('No audit logs to delete.');
            return 0;
        }
        
        AuditLog::where('created_at', '<', now()->subDays($days))->delete();
        
        $this->info("Deleted {$count} old audit logs.");
        return 0;
    }
}
