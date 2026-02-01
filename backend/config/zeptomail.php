<?php
return [
    'token' => env('ZEPTOMAIL_TOKEN'),
    'region' => env('ZEPTOMAIL_REGION', 'us'),
    'bounce_address' => env('ZEPTOMAIL_BOUNCE_ADDRESS'),
];
