<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class LogService
{

    /**
     * Upload the Laravel log file to the S3 storage
     * @return void
     */

    public function logToS3()
    {
        try {
            $localPath = storage_path('logs/laravel.log');
            $s3Path = 'logs/laravel_' . now()->format('Y-m-d_H-i-s') . '.log';
            if (!file_exists($localPath)) {
                return;
            }
            Storage::disk(config('filesystems.storage_service'))->put($s3Path, file_get_contents($localPath));
        } catch (\Exception $e) {
            Log::error('Error in laravelLogsToS3Command: handle function:' . $e->getMessage());
        }
    }
}
