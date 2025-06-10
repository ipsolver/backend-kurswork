<?php
namespace core;
use core\Config;

class helper
{
    public static function asset(string $path) : string 
    {
    $base = Config::get()->baseURL;
    return $base.ltrim($path, '/');
    }
}
