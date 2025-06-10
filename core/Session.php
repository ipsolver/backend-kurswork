<?php
namespace core;

class Session
{
    public function __construct()
    {
        ini_set('session.gc_maxlifetime', 86400);
        ini_set('session.cookie_lifetime', 86400);
        session_set_cookie_params(86400);
        if(session_status() === PHP_SESSION_NONE) 
            session_start();
    }

    public function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function remove($name)
    {
        unset($_SESSION[$name]);
    } 

    public function setValues(array $assocArray)
    {
        foreach($assocArray as $key => $value)
        {
            $this->set($key, $value);
        }
    }

    public function get($name)
    {
        if(empty($_SESSION[$name]))
            return null;
        return $_SESSION[$name];
    }

    
}