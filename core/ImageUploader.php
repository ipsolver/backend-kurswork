<?php

namespace core;

class ImageUploader extends BaseImageUploader
{
    protected static string $uploadDir = 'assets/uploads/avatars/';
    protected static string $defaultImage = 'assets/img/default-avatar.png';

    protected static function generateFileName(string $username, string $extension): string
    {
        $rand = rand(99, 9999);
        return "{$username}{$rand}_avatar.{$extension}";
    }

    public static function isDefaultAvatar(string $path): bool
    {
        return self::isDefault($path);
    }
}
