<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InterventionImage;

class ImageService
{
    public static function upload($imageFile, $folderName)
    {
        $fileName = uniqid(rand().'_'); // ランダム文字を生成
        $extension = $imageFile->extension(); // 拡張子を取得
        $fileNameToStore = $fileName. '.' . $extension; // ランダム文字と拡張子をくっつける
        $resizedImage = InterventionImage::make($imageFile)->resize(1920, 1080)->encode(); // リサイズ処理

        Storage::put('public/' . $folderName . '/' . $fileNameToStore, $resizedImage );

        return $fileNameToStore;
    }
}