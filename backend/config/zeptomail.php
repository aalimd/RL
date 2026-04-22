<?php
return [
    'token' => env('ZEPTOMAIL_TOKEN'),
    'host' => env('ZEPTOMAIL_HOST', 'zoho.com'),
    'region' => env('ZEPTOMAIL_REGION', 'us'),
    'bounce_address' => env('ZEPTOMAIL_BOUNCE_ADDRESS'),
];
