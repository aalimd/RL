<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\DatabaseBackupMail;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--email= : The email address to send the backup to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a full database backup and email it to the admin.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $email = $this->option('email');

        // If no email provided, try to get from config or fallback to a default admin email
        if (!$email) {
            // You might want to grab the first admin user's email if none provided
            $admin = \App\Models\User::where('role', 'admin')->first();
            if ($admin) {
                $email = $admin->email;
            } else {
                $this->error('No email provided and no admin user found.');
                return 1;
            }
        }

        $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $tempPath = storage_path('app/backups/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        try {
            $handle = fopen($tempPath, 'w');

            // Header info
            fwrite($handle, "-- Database Backup\n");
            fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");

            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tables = array_map('reset', $tables);

            $bar = $this->output->createProgressBar(count($tables));
            $bar->start();

            foreach ($tables as $table) {
                // Structure
                fwrite($handle, "-- Table structure for `$table`\n");
                fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n");

                $createTable = DB::select("SHOW CREATE TABLE `$table`")[0]->{'Create Table'};
                fwrite($handle, $createTable . ";\n\n");

                // Data
                fwrite($handle, "-- Dumping data for `$table`\n");

                DB::table($table)->orderBy('id')->chunk(100, function ($rows) use ($handle, $table) {
                    foreach ($rows as $row) {
                        $values = array_map(function ($value) {
                            if (is_null($value))
                                return 'NULL';
                            return "'" . addslashes($value) . "'";
                        }, (array) $row);

                        $sql = "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                        fwrite($handle, $sql);
                    }
                });

                fwrite($handle, "\n");
                $bar->advance();
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);
            $bar->finish();
            $this->newLine();

            $this->info('Backup generated successfully.');

            // Send Email
            $this->info("Sending email to $email...");
            Mail::to($email)->send(new DatabaseBackupMail($tempPath));

            $this->info('Email sent.');

            // Log Success
            Log::info("Automated backup sent to $email");

            // Clean up
            unlink($tempPath);
            $this->info('Temporary backup file cleaned up.');

            return 0;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            Log::error('Automated backup failed: ' . $e->getMessage());

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            return 1;
        }
    }
}
