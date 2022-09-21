<?php

// config for Sawirricardo/Filepond
return [
    'server_url' => 'laravel-filepond',
    'disk' => 'local',
    'rules' => ['required', 'file', 'max:12288'], // 12MB
    'directory' => 'filepond-tmp',
    'chunk_directory' => 'filepond-chunks',
    'middleware' => ['web', 'throttle:60,1'],
    'preview_mimes' => [
        // Supported file types for temporary pre-signed file URLs.
        'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
        'mov', 'avi', 'wmv', 'mp3', 'm4a',
        'jpg', 'jpeg', 'mpga', 'webp', 'wma',
    ],
    'max_upload_time' => 5, // Max duration (in minutes) before an upload gets invalidated.
];
