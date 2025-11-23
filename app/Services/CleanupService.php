<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CleanupService
{
    protected string $file;
    protected string $logFile;

    public function __construct()
    {
        $this->file = storage_path('app/last_cleanup.txt');
        $this->logFile = storage_path('app/cleanup.log');

        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($this->file)) {
            file_put_contents($this->file, 0);
        }

        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, "=== Cleanup Log Initialized ===\n");
        }
    }

    public function runIfDue(): void
    {
        // Flag de controle via ENV
        if (!env('CLEANUP_ENABLED', true)) {
            file_put_contents($this->logFile, "Cleanup skipped (disabled by ENV)\n", FILE_APPEND);
            return;
        }

        $interval = (int) env('CLEANUP_INTERVAL', 86400);
        $lastCleanup = (int) file_get_contents($this->file);

        if ((time() - $lastCleanup) < $interval) {
            return;
        }

        $tablesTTL = [];
        $envTables = env('CLEANUP_TABLES', '');
        if ($envTables) {
            foreach (explode(',', $envTables) as $pair) {
                [$table, $ttl] = explode(':', $pair);
                $tablesTTL[trim($table)] = (int) trim($ttl);
            }
        }

        $now = time();
        $logEntries = ["Cleanup executed at " . date('Y-m-d H:i:s', $now)];

        foreach ($tablesTTL as $table => $ttl) {
            $deleted = 0;

            switch ($table) {
                case 'user_tokens':
                case 'access_tokens':
                    $threshold = date('Y-m-d H:i:s', $now - $ttl);
                    $deleted = DB::table($table)
                        ->where('expires_at', '<', $threshold)
                        ->delete();
                    break;

                case 'sessions':
                    $deleted = DB::table($table)
                        ->where('last_activity', '<', $now - $ttl)
                        ->delete();
                    break;

                case 'password_reset_tokens':
                    $threshold = date('Y-m-d H:i:s', $now - $ttl);
                    $deleted = DB::table($table)
                        ->where('created_at', '<', $threshold)
                        ->delete();
                    break;

                default:
                    break;
            }

            $logEntries[] = "Table '{$table}': {$deleted} rows deleted";
        }

        file_put_contents($this->file, $now);
        file_put_contents($this->logFile, implode("\n", $logEntries) . "\n", FILE_APPEND);
    }
}
