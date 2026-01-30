<?php
/**
 * Laravel Application Entry Point
 * Redirects all requests to Laravel while maintaining clean URLs
 */

// Change the current directory to Laravel's public folder
chdir(__DIR__ . '/backend/public');

// Include Laravel's entry point
require __DIR__ . '/backend/public/index.php';
