<?php

namespace App\Controllers;

use Framework\Http\Request;

use Framework\Support\Facades\Storage;

class UploadController extends Controller
{
    const ALLOWED_EXTENSIONS = ['png', 'avif', 'gif', 'jpg', 'jpeg', 'webp', 'heic'];

    private function storeLog($state, $originalName, $originalExtension, $newFileName = null)
    {
        $date = new \DateTime();
        $date = $date->format('Y-m-d H:i:s');

        $messageFormats = [
            'success' => "%s | User from IP %s successfully uploaded image with original name '%s' and extension '%s'. New file name: '%s'",
            'fail' => "%s | User from IP %s unsuccessfully uploaded image with original name '%s' and extension '%s'. New file name would be: '%s'",
            'warning' => "%s | User from IP %s tried to upload file with original name '%s' and unallowed extension '%s'.",
        ];

        $message = sprintf(
            $messageFormats[$state],
            strtoupper($state),
            request()->getClientIp(),
            $originalName,
            $originalExtension,
            $newFileName
        );

        $details = sprintf(
            "[%s] %s",
            $date,
            $message
        );

        Storage::disk('log')->writeTextFile("uploads.log", $details, true, true);
    }

    public function upload($request)
    {
        $hasErrorOccured = false;

        foreach($request->files->all()['images'] as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            if (!in_array($extension, static::ALLOWED_EXTENSIONS)) {
                $hasErrorOccured = true;
                $this->storeLog('warning', $originalName, $extension);
                continue;
            }

            $fileName = substr(md5(uniqid($originalName, true)), 0, 16) . '.' . $extension;

            if ($fileSuccessfullyUploaded = Storage::disk('public')->saveFile($file, '', $fileName)) {
                $this->storeLog('success', $originalName, $extension, $fileName);
            } else {
                $hasErrorOccured = true;
                $this->storeLog('fail', $originalName, $extension, $fileName);
            }
        }

        if (!$hasErrorOccured) {
            return response()->json([
                'status' => "Success",
                'message' => "File upload completed successfully."
            ], 200);
        } else {
            return response()->json([
                'status' => "Error",
                'message' => "There was a problem processing the uploaded file."
            ], 422);
        }
    }
}