<?php

namespace App\Core\File\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class FileManagementService
{
    /**
     * Upload a file to a specified path in the public directory.
     *
     * @param UploadedFile $file
     * @param string $path
     * @return string
     */
    public function upload(UploadedFile $file, string $path): string
    {
        $destinationPath = public_path($path);
        if (!File::isDirectory($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true, true);
        }

        $fileName = time() . '_' . $file->getClientOriginalName();
        $destinationFile = $destinationPath . '/' . $fileName;

        // We use copy() instead of $file->move() because move() internally uses rename(),
        // which often fails in Docker environments when the source (storage/app/livewire-tmp)
        // and destination (public/organization) are on different partitions or mount points.
        if (!File::copy($file->getRealPath(), $destinationFile)) {
            throw new \RuntimeException("Could not move the file to destination: {$destinationFile}");
        }
        @unlink($file->getRealPath());

        return $path . '/' . $fileName;
    }

    /**
     * Delete a file from the public directory.
     *
     * @param string|null $path
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if ($path && File::exists(public_path($path))) {
            return File::delete(public_path($path));
        }
        return false;
    }
}