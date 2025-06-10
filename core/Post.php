<?php

namespace core;

class Post extends RequestMethod
{
    public function __construct()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        if (stripos($contentType, 'application/json') !== false) 
        {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) 
            {
                parent::__construct($data);
                return;
            }
        }

        parent::__construct($_POST);
    }
}