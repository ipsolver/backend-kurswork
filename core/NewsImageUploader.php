<?php

namespace core;

class NewsImageUploader extends BaseImageUploader
{
    protected static string $uploadDir = 'assets/uploads/news/';
    protected static string $defaultImage = '/crystal/assets/img/default-new.png';

    protected static function generateFileName(string $title, string $extension): string
    {
        $rand1 = rand(100, 1000);
        $rand2 = rand(0, 10);
        // $cleanTitle = preg_replace('/[^a-zA-Z0-9]/', '', mb_strtolower($title));
        return "{$rand1}{$title}{$rand2}_news.{$extension}";
    }

    public static function isDefaultNewsImage(string $path): bool
    {
        return self::isDefault($path);
    }
}