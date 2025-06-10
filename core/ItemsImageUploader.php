<?php

namespace core;

class ItemsImageUploader extends BaseImageUploader
{
    protected static string $uploadDir = 'assets/uploads/items/';
    protected static string $defaultImage = '/crystal/assets/img/default-item.png';

    protected static function generateFileName(string $code, string $extension): string
    {
        $rand1 = rand(100, 1000);
        $rand2 = rand(0, 10);
        $cleanTitle = preg_replace('/[^a-zA-Z0-9]/', '', mb_strtolower($code));
        return "{$rand1}{$code}{$rand2}_item.{$extension}";
    }

    public static function isDefaultImage(string $path): bool
    {
        return self::isDefault($path);
    }

    public static function getDefaultImage(): string
    {
        return self::$defaultImage;
    }

    public static function uploadMultiple(array $files, string $code, array $imageIds): array
    {
        $paths = [];
        $uploadDirAbsolute = dirname(__DIR__, 1) . '/' . static::$uploadDir . $code . '/';

        if (!file_exists($uploadDirAbsolute))
            mkdir($uploadDirAbsolute, 0777, true);

        foreach ($files['tmp_name'] as $index => $tmpName)
        {
            if ($index >= 4) 
                break;
            if (!is_uploaded_file($tmpName)) 
                continue;

            $extension = self::getFileExtension($tmpName);
            if (!$extension)
                continue;

            $id_image = $imageIds[$index] ?? null;
            if (!$id_image)
                continue;

            $fileName = static::generateFileName("{$code}_{$id_image}", $extension);
            $absolutePath = $uploadDirAbsolute . $fileName;
            $webPath = '/crystal/' . static::$uploadDir . $code . '/' . $fileName;

            if (move_uploaded_file($tmpName, $absolutePath))
                $paths[] = ['id' => $id_image, 'path' => $webPath];
        }

        return $paths;
    }


    public static function deleteItemFolder(string $code): void
    {
        $folder = $_SERVER['DOCUMENT_ROOT'] . '/crystal/' . static::$uploadDir . $code . '/';
        if (is_dir($folder)) 
        {
            $files = glob($folder . '*');
            foreach ($files as $file)
                if (is_file($file)) 
                    unlink($file);
            rmdir($folder);
        }
    }

    public static function hasUploading(array $files, string $code, int $itemId, int $mainIndex = 0): void
    {
        $validMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];

        $hasUploaded = false;
        $limitedFiles = [
            'name' => [],
            'type' => [],
            'tmp_name' => [],
            'error' => [],
            'size' => []
        ];

        for ($i = 0; $i < count($files['name']) && count($limitedFiles['name']) < 4; $i++) 
        {
            if ($files['error'][$i] === UPLOAD_ERR_OK && in_array($files['type'][$i], $validMimeTypes)) 
            {
                $hasUploaded = true;
                $limitedFiles['name'][] = $files['name'][$i];
                $limitedFiles['type'][] = $files['type'][$i];
                $limitedFiles['tmp_name'][] = $files['tmp_name'][$i];
                $limitedFiles['error'][] = $files['error'][$i];
                $limitedFiles['size'][] = $files['size'][$i];
            }
        }

        if ($hasUploaded) 
        {
            $imageIds = [];
            for ($i = 0; $i < count($limitedFiles['name']); $i++) 
            {
                $isMain = $i === $mainIndex ? 1 : 0;
                $imageIds[$i] = Core::get()->db->insert('item_images', [
                    'item_id' => $itemId,
                    'path' => ' ',
                    'is_main' => $isMain
                ]);
            }

            if (!empty($imageIds)) 
            {
                $uploaded = self::uploadMultiple($limitedFiles, $code, $imageIds);
                foreach ($uploaded as $img)
                    Core::get()->db->update('item_images', ['path' => $img['path']], ['id' => $img['id']]);
            }
        }

        if (!$hasUploaded) 
        {
            Core::get()->db->insert('item_images', [
                'item_id' => $itemId,
                'path' => self::getDefaultImage(),
                'is_main' => 1
            ]);
        }
    }





}