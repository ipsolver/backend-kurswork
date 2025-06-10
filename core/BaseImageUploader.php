<?php

namespace core;

abstract class BaseImageUploader
{
    protected static string $uploadDir;
    protected static string $defaultImage;

    protected static function isValidUpload(array $file): bool
    {
        return isset($file['tmp_name'], $file['error']) &&
               $file['error'] === UPLOAD_ERR_OK &&
               is_uploaded_file($file['tmp_name']);
    }

    protected static function getFileExtension(string $tmpPath): ?string
    {
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $mimeType = mime_content_type($tmpPath);
        return $allowedTypes[$mimeType] ?? null;
    }

    public static function deleteIfExists(string $path): void
    {
        if (!self::isDefault($path)) 
        {
            $relativePath = preg_replace('#^/crystal/#', '', ltrim($path, '/'));
            $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $path;

            if (file_exists($absolutePath) && is_file($absolutePath)) 
            {
                
                unlink($absolutePath);
            }
        }
    }


    public static function isDefault(string $path): bool
    {
        return $path === static::$defaultImage;
    }

    abstract protected static function generateFileName(string $name, string $extension): string;

    public static function upload(array $file, string $name): string
{
    // var_dump($file);
    // die;
    if (!self::isValidUpload($file))
        return static::$defaultImage;

    $extension = self::getFileExtension($file['tmp_name']);
    if (!$extension)
        return static::$defaultImage;

    // Абсолютний шлях для запису файлу
    $uploadDirAbsolute = dirname(__DIR__, 1) . '/'. static::$uploadDir;
    if (!file_exists($uploadDirAbsolute))
        mkdir($uploadDirAbsolute, 0777, true);

    $fileName = static::generateFileName($name, $extension);

    $absolutePath = $uploadDirAbsolute . $fileName;
    $webPath = '/crystal/' . static::$uploadDir . $fileName;

    return move_uploaded_file($file['tmp_name'], $absolutePath)
        ? $webPath
        : static::$defaultImage;
}


    public static function handleUpload(string $inputName, string $name): string
    {
        // var_dump($_FILES[$inputName]);
        // die;
        return isset($_FILES[$inputName])
            ? static::upload($_FILES[$inputName], $name)
            : static::$defaultImage;
    }

    public static function getDefaultImage(): string
    {
        return static::$defaultImage;
    }


}
