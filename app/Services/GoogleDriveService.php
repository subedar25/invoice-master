<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class GoogleDriveService
{
    public function upload(UploadedFile $file): string
    {
        /**
         * TODO:
         * - Upload to Google Drive
         * - Return Google Drive file ID
         */

        // TEMP: simulate upload (for now)
        return 'drive_' . uniqid();
    }
}
