<?php

namespace App\Services\Image;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public static function singleUpload($file, $path, $file_system)
    {
        $uniqid = uniqid();
        $path = $file->storeAs($path, $uniqid.'.'. $file->extension(), $file_system);
        Log::info(['url' => 'storage/'.$path]);
        return 'storage/'.$path;
        // return asset('storage/'.$path);
        // return 'storage/'.$path;
    }

    public static function multipleUpload(array $files, string $path, string $file_system)
    {
        foreach($files as $key=>$file) {
            $storedUrl = $file['file']->storeAs(
                $path,
                $file['type'].'.'. $file['file']->extension(),
                $file_system
            );
            $files[$key]['url'] = $storedUrl;
        }
        return $files;
    }

    public static function base64ImageStore($image, $disk = 'public', $user_dir = 'tmp_images')
    {
        $img = $image;
        $folderPath = "images/"; // path location

        $image_parts = explode(";base64,", $img);

        if (count($image_parts) != 2) {
            return false; // Invalid image format
        }

        $image_type_aux = explode("image/", $image_parts[0]);

        if (count($image_type_aux) != 2) {
            return false; // Invalid image format
        }

        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);

        if ($image_base64 === false) {
            return false; // Invalid base64 image
        }

        $uniqid = uniqid();
        $isStored = Storage::disk($disk)->put('/'.$user_dir.'/'.$uniqid.'.'.$image_type, $image_base64);

        if ($isStored) {
            return 'storage/'.$disk.'/'.$user_dir.'/'. $uniqid.'.'.$image_type;
        }

        return false;
    }
}
