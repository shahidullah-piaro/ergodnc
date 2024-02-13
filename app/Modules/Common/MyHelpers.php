<?php

declare(strict_types=1);

namespace App\Modules\Common;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

abstract class MyHelpers
{
    public static function nullStringToInt($str) : ?int
    {
        if ($str !== null) {
            return (int)$str;
        }

        return null;
    }

    public static function saveImage($image) : string
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($image, strpos($image, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with avatar data');
        }

        // Save the image in local path
        $localDir = 'app/public/images/';  // Adjust this path as needed for your local storage
        $serverDir = 'images/';  // Adjust this path as needed for your server
        $file = Str::random() . '.' . $type;
        $localPath = storage_path($localDir);  // Use storage_path() for local storage
        $relativePath = $serverDir . $file;

        if (!File::exists($localPath)) {
            File::makeDirectory($localPath, 0755, true);
        }

        file_put_contents($localPath . $file, $image);  // Use $localPath for saving

        return $relativePath;
    }

    public static function saveFile($file) : string
    {
        // Check for valid base64 string and data URI with file data
        if (!preg_match('/^data:application\/(pdf|msword|vnd.openxmlformats-officedocument.wordprocessingml.document);base64,/', $file, $type)) {
            throw new \Exception('Invalid base64 file or incorrect format');
        }

        // Extract base64 encoded text and extension
        $file = substr($file, strpos($file, ',') + 1);
        $type = strtolower($type[1]); // pdf, doc, docx

        // Check for allowed file types
        if (!in_array($type, ['pdf', 'doc', 'docx'])) {
            throw new \Exception('Invalid file type');
        }

        // Decode base64 data
        $file = str_replace(' ', '+', $file);
        $decodedFile = base64_decode($file);

        if ($decodedFile === false) {
            throw new \Exception('base64_decode failed');
        }
        
        // Save the file to a local path
        $localDir = 'app/public/documents/';  // Adjust this path as needed for your local storage
        $serverDir = 'documents/';  // Adjust this path as needed for your server
        $filename = Str::random() . '.' . $type;
        $localPath = storage_path($localDir);  // Use storage_path() for local storage
        $relativePath = $serverDir . $filename;

        if (!File::exists($localPath)) {
            File::makeDirectory($localPath, 0755, true);
        }

        file_put_contents($localPath . $filename, $decodedFile);  // Use $localPath for saving

        return $relativePath;
    }
}
