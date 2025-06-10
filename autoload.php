<?php
spl_autoload_register(function ($myclass) 
{
    $path = str_replace('\\', '/', $myclass) .'.php';

    if (file_exists($path)) 
    {
        include_once $path;
    } 
    // else 
    // {
    //     die("Файл $path не знайдено");
    // }
});