<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $backupPath;
    public $backupDate;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($backupPath)
    {
        $this->backupPath = $backupPath;
        $this->backupDate = date('Y-m-d H:i:s');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('💾 Database Backup: ' . $this->backupDate)
            ->view('emails.database_backup')
            ->attach($this->backupPath, [
                'as' => 'backup_' . date('Y-m-d') . '.sql',
                'mime' => 'application/sql',
            ]);
    }
}
