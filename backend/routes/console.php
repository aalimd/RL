<?php

use Illuminate\Support\Facades\Schedule;

// Schedule the database backup to run weekly
// The command will automatically find the first admin email if none is provided
Schedule::command('backup:database')->weekly();
