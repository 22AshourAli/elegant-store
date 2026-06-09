<?php

return [

    /*
     * The backup destination. Disks configured in filesystems.php.
     */
    'backup' => [
        'destination' => [
            'disks' => [
                // 's3',           // Production: AWS S3
                // 'gcs',          // Google Cloud Storage
                'local',        // Dev/staging fallback
            ],
        ],

        /*
         * The name for the backup files. Timestamp will be appended.
         */
        'name' => env('APP_NAME', 'elegant-store') . '-backup',

        /*
         * Source files/directories to exclude from the backup.
         */
        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('.git'),
                    storage_path('debugbar'),
                ],

                /*
                 * If symlinks should be followed.
                 */
                'follow_links' => false,
            ],

            /*
             * Databases to include in the backup.
             */
            'databases' => [
                'pgsql',
            ],
        ],

        /*
         * Cleanup: keep only the latest N backups.
         */
        'cleanup' => [
            'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
            'default_strategy' => [
                'keep_all_backups_for_days' => 7,
                'keep_daily_backups_for_days' => 30,
                'keep_weekly_backups_for_weeks' => 8,
                'keep_monthly_backups_for_months' => 4,
                'keep_yearly_backups_for_years' => 2,
                'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
            ],
        ],

        /*
         * Notifications for backup failures/successes.
         */
        'notifications' => [
            'notifications' => [
                \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => ['mail'],
                \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => ['mail'],
                \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => ['mail'],
                \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => [],
                \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => [],
            ],

            'mail' => [
                'to' => env('BACKUP_MAIL_TO', 'admin@elegantstore.test'),
            ],
        ],

        /*
         * Custom health checks for the backup.
         */
        'monitor_backups' => [
            [
                'name' => env('APP_NAME', 'elegant-store') . '-backup',
                'disks' => ['local'],
                'health_checks' => [
                    \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,
                    \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
                ],
            ],
        ],

        /*
         * Signals to stop the backup when a specific event happens.
         */
        'signals' => [
            'timeout' => 300, // 5 minutes
        ],
    ],
];
