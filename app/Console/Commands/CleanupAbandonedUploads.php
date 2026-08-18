<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CleanupAbandonedUploads extends Command
{
    /**
     * Command name.
     */
    protected $signature = 'uploads:cleanup
                            {--hours=3 : Delete chunk sessions older than this many hours}';

    /**
     * Command description.
     */
    protected $description =
        'Delete abandoned chunk upload sessions and temporary files';

    /**
     * Execute command.
     */
    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | Cleanup Age
        |--------------------------------------------------------------------------
        |
        | Allow 0 for testing.
        |
        */
        $hours = max(
            0,
            (int) $this->option('hours')
        );

        $cutoffTime = now()
            ->subHours($hours)
            ->timestamp;

        $disk = Storage::disk('local');

        $basePath = 'temp/chunks';

        if (!$disk->exists($basePath)) {

            $this->info(
                'No temporary chunk directory found.'
            );

            return self::SUCCESS;
        }

        $directories = $disk->directories(
            $basePath
        );

        $deletedSessions = 0;

        $deletedChunks = 0;

        $freedBytes = 0;

        foreach ($directories as $directory) {

            try {

                $files = $disk->files(
                    $directory
                );

                /*
                |--------------------------------------------------------------------------
                | Ignore Empty Directories
                |--------------------------------------------------------------------------
                */

                if (empty($files)) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Find Last Modification Time
                |--------------------------------------------------------------------------
                */

                $latestModified = 0;

                foreach ($files as $file) {

                    $modified = $disk->lastModified(
                        $file
                    );

                    $latestModified = max(
                        $latestModified,
                        $modified
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Check Whether Session Is Abandoned
                |--------------------------------------------------------------------------
                */

                if ($latestModified >= $cutoffTime) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Calculate Storage Before Delete
                |--------------------------------------------------------------------------
                */

                $sessionSize = 0;

                foreach ($files as $file) {

                    $sessionSize += $disk->size(
                        $file
                    );
                }

                $chunkCount = count($files);

                /*
                |--------------------------------------------------------------------------
                | Delete Session
                |--------------------------------------------------------------------------
                */

                $disk->deleteDirectory(
                    $directory
                );

                $deletedSessions++;

                $deletedChunks += $chunkCount;

                $freedBytes += $sessionSize;

                $this->line(
                    "Deleted: {$directory} " .
                    "({$chunkCount} chunks)"
                );

            } catch (Throwable $e) {

                Log::error(
                    'Failed to cleanup abandoned upload.',
                    [
                        'directory' => $directory,
                        'error' => $e->getMessage(),
                    ]
                );

                $this->error(
                    "Failed to delete {$directory}: " .
                    $e->getMessage()
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Output Summary
        |--------------------------------------------------------------------------
        */

        $formattedSize = $this->formatBytes(
            $freedBytes
        );

        $this->newLine();

        $this->info(
            'Abandoned upload cleanup completed.'
        );

        $this->table(
            [
                'Deleted Sessions',
                'Deleted Chunks',
                'Freed Storage',
            ],
            [
                [
                    $deletedSessions,
                    $deletedChunks,
                    $formattedSize,
                ],
            ]
        );

        Log::info(
            'Abandoned upload cleanup completed.',
            [
                'deleted_sessions' => $deletedSessions,
                'deleted_chunks' => $deletedChunks,
                'freed_bytes' => $freedBytes,
                'freed_storage' => $formattedSize,
                'hours' => $hours,
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Format bytes.
     */
    private function formatBytes(
        int $bytes
    ): string {

        if ($bytes <= 0) {
            return '0 Bytes';
        }

        $units = [
            'Bytes',
            'KB',
            'MB',
            'GB',
            'TB',
        ];

        $power = floor(
            log($bytes, 1024)
        );

        $power = min(
            $power,
            count($units) - 1
        );

        return round(
            $bytes / pow(1024, $power),
            2
        ) . ' ' . $units[$power];
    }
}