<?php

namespace core;

class OrdersImageUploader extends BaseImageUploader
{
    protected static string $uploadDir = 'assets/uploads/orders/';
    protected static string $defaultImage = '/crystal/assets/img/default-order.png';

    protected static function generateFileName(string $name, string $extension): string
    {
        [$username, $orderId] = explode('_', $name);
        $rand = rand(99, 999);
        return "{$rand}{$username}{$orderId}_order.{$extension}";
    }

    public static function uploadSingle(array $file, string $username, int $orderId): ?string
    {
        if (!is_uploaded_file($file['tmp_name']))
            return null;

        $extension = self::getFileExtension($file['tmp_name']);
        if (!$extension)
            return null;

        $code = "{$username}_{$orderId}";
        $fileName = static::generateFileName($code, $extension);

        $uploadDirAbsolute = dirname(__DIR__, 1) . '/' . static::$uploadDir . $username . '/';
        if (!file_exists($uploadDirAbsolute))
            mkdir($uploadDirAbsolute, 0777, true);

        $absolutePath = $uploadDirAbsolute . $fileName;
        $webPath = '/crystal/' . static::$uploadDir . $username . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $absolutePath))
            return $webPath;

        return null;
    }

    public static function deleteOrderFolder(string $username): void
    {
        $folder = $_SERVER['DOCUMENT_ROOT'] . '/crystal/' . static::$uploadDir . $username . '/';
        if (is_dir($folder)) 
        {
            $files = glob($folder . '*');
            foreach ($files as $file) 
            {
                if (is_file($file))
                    unlink($file);
            }
            rmdir($folder);
        }
    }

    public static function deleteOrderImage(string $username, int $orderId): void
    {
        $folder = $_SERVER['DOCUMENT_ROOT'] . '/crystal/' . static::$uploadDir . $username . '/';

        if (!is_dir($folder))
            return;

        // $pattern = "/^\d+{$username}{$orderId}_order\.(jpg|jpeg|png|gif)$/i";
        $pattern = "/^.*{$username}.*{$orderId}_order\.(jpg|jpeg|png|gif)$/i";

        $files = glob($folder . '*');
        foreach ($files as $file) 
        {
            if (is_file($file) && preg_match($pattern, basename($file))) 
            {
                unlink($file);
                break;
            }
        }

        $remainingFiles = glob($folder . '*');
        if (empty($remainingFiles))
            rmdir($folder);
    }


    public static function getDefaultImage(): string
    {
        return self::$defaultImage;
    }

    public static function isDefaultImage(string $path): bool
    {
        return self::isDefault($path);
    }
}
