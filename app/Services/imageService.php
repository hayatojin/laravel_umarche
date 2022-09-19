<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use InterventionImage;

class ImageService
{
    public static function upload($imageFile, $folderName)
    {
        // dd($imageFile['image']);

        // 画像が複数入ってきた時用の対策（画像が配列かどうかを確認し、配列の場合は要素を取得。配列でない場合はそのまま使う）
        if(is_array($imageFile))
        {
            $file = $imageFile['image'];
        }else {
            $file = $imageFile;
        }

        $fileName = uniqid(rand().'_'); // ランダム文字を生成
        $extension = $file->extension(); // 拡張子を取得
        $fileNameToStore = $fileName. '.' . $extension; // ランダム文字と拡張子をくっつける
        $resizedImage = InterventionImage::make($file)->resize(1920, 1080)->encode(); // リサイズ処理

        Storage::put('public/' . $folderName . '/' . $fileNameToStore, $resizedImage );

        return $fileNameToStore;
    }
}