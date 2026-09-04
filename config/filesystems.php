<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => mb_rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Spatie media library
        'media' => [
            'driver' => env('MEDIA_DISK_DRIVER', 'local'),
            'root' => storage_path(env('MEDIA_DISK_ROOT_PATH', 'app/public/media')),
            'url' => mb_rtrim(env('APP_URL', 'http://localhost'), '/').'/'.env('MEDIA_DISK_URL_PATH', 'storage/media'),
            'visibility' => env('MEDIA_DISK_VISIBILITY', 'public'),
            'throw' => env('MEDIA_DISK_THROW', false),
            'report' => env('MEDIA_DISK_REPORT', false),
        ],

        // Spatie backups
        'backups' => [
            'driver' => env('BACKUP_DISK_DRIVER', 'local'),
            'root' => storage_path(env('BACKUP_DISK_ROOT_PATH', 'app/private/backups')),
            'url' => mb_rtrim(env('APP_URL', 'http://localhost'), '/').'/'.env('BACKUP_DISK_URL_PATH', 'storage/backups'),
            'visibility' => env('BACKUP_DISK_VISIBILITY', 'private'),
            'throw' => env('BACKUP_DISK_THROW', false),
            'report' => env('BACKUP_DISK_REPORT', false),
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
