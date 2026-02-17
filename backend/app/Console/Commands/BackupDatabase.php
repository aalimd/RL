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
            $pdo = DB::connection()->getPdo();

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
                $escapedTable = $this->escapeSqlIdentifier((string) $table);

                // Structure
                fwrite($handle, "-- Table structure for `{$escapedTable}`\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$escapedTable}`;\n");

                $createTableResult = DB::select("SHOW CREATE TABLE `{$escapedTable}`");
                if (!isset($createTableResult[0])) {
                    continue;
                }

                $createTableParts = array_values((array) $createTableResult[0]);
                $createTable = $createTableParts[1] ?? null;
                if (!$createTable) {
                    continue;
                }

                fwrite($handle, $createTable . ";\n\n");

                // Data
                fwrite($handle, "-- Dumping data for `{$escapedTable}`\n");

                $columns = DB::connection()->getSchemaBuilder()->getColumnListing((string) $table);
                if (empty($columns)) {
                    fwrite($handle, "\n");
                    $bar->advance();
                    continue;
                }

                $escapedColumns = array_map(fn($column) => '`' . $this->escapeSqlIdentifier((string) $column) . '`', $columns);
                $columnList = implode(', ', $escapedColumns);

                foreach (DB::table($table)->select($columns)->cursor() as $row) {
                    $rowData = (array) $row;
                    $values = [];
                    foreach ($columns as $column) {
                        $values[] = $this->quoteSqlValue($pdo, $rowData[$column] ?? null);
                    }

                    $sql = "INSERT INTO `{$escapedTable}` ({$columnList}) VALUES (" . implode(', ', $values) . ");\n";
                    fwrite($handle, $sql);
                }

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

    /**
     * Escape SQL identifier (table/column) for MySQL-style dumps.
     */
    private function escapeSqlIdentifier(string $identifier): string
    {
        return str_replace('`', '``', $identifier);
    }

    /**
     * Quote SQL values safely using PDO.
     */
    private function quoteSqlValue(\PDO $pdo, $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if ($value instanceof \DateTimeInterface) {
            return $pdo->quote($value->format('Y-m-d H:i:s'));
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }
}
