<?php

namespace core;

class Router
{
    protected $route;
    public function __construct ($route)
    {
        $this->route = $route ?: 'home/index';
    }
    public function run()
    {
        $this->route = trim($this->route, "/ \t\n\r\0\x0B"); 
        $parts = explode('/', $this->route);
        if (strtolower($this->route) === 'index') 
        {
            header('Location: /crystal/home');
            exit;
        }
        if(strlen($parts[0]) == 0)
        {
            $parts[0] = "home";
            $parts[1] = "index";
        }
        \core\Core::get()->moduleName = $parts[0];
        \core\Core::get()->actionName = $parts[1] ?? 'index';
        $controller = 'controllers\\' . ucfirst($parts[0]) . 'Controller';
        $method = 'action' . ucfirst($parts[1] ?? 'Index');
        if (class_exists($controller)) 
        {
            $controllerObject = new $controller();
            Core::get()->controllerObject = $controllerObject;
            if (method_exists($controllerObject, $method)) 
            {
                if (!$controllerObject->beforeAction($parts[1] ?? 'index'))
                    return;
                
                array_splice($parts, 0, 2);

                return $params = $controllerObject->$method($parts);
            } 
            else 
            {
                $this->error(404);
            }
        } 
        else 
        {
            $this->error(404);
            //!!!! return зробити!
        }
    }

public function done()
{

}

public function error($code)
    {
        http_response_code($code);
        \core\Core::get()->logRequest($code);
        

        $errorPage = "views/errors/{$code}.php";

        if (file_exists($errorPage)) 
        {
            include $errorPage;
        } 
        else
        {
            echo "$code - Помилка";
        }

        exit;

    }
}