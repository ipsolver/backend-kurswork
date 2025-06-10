<?php

namespace core;

class Template
{
    protected $templateFilePath;
    protected $paramsArray;
    public Controller $controller;

    
    public function __construct($templateFilePath)
    {
        $this->templateFilePath = $templateFilePath;
        $this->paramsArray = [];
    }

    public function __set($name, $value)
    {
        Core::get()->template->setParam($name, $value);
    }

    public function setParam($paramName, $paramValue)
    {
        $this->paramsArray[$paramName] = $paramValue;
    }

    public function setParams($params)
    {
        foreach ($params as $key => $value)
            $this->setParam($key, $value);
    }

    public function setTemplateFilePath($path)
    {
        $this->templateFilePath = $path;
    }

    // public function getHTML()
    // {
    //     ob_start();
    //     $this->controller = \core\Core::get()->controllerObject;
    //     extract($this->paramsArray);
    //     include($this->templateFilePath);
    //     $str = ob_get_contents();
    //     ob_end_clean();
    //     return $str;
    // }

    public function getHTML()
    {
        $core = \core\Core::get();
        $module = $core->moduleName;
        $action = $core->actionName;

        $cacheRoutes = [
            'home/about'
        ];

        $routeKey = "$module/$action";
        $cachePath = __DIR__ . "/../cache/" . str_replace('/', '_', $routeKey) . ".php";

        if (in_array($routeKey, $cacheRoutes)) 
        {
            if (http_response_code() === 200 && file_exists($cachePath) && file_exists($this->templateFilePath))
                return file_get_contents($cachePath);
            else if(file_exists($cachePath) && !file_exists($this->templateFilePath))
            {
                unlink($cachePath);
                $core->router->error(404);
                exit;
            }
        }
        if (file_exists($this->templateFilePath)) 
        {
            ob_start();
            $this->controller = $core->controllerObject;
            extract($this->paramsArray);
            include($this->templateFilePath);
            $html = ob_get_clean();

            // Збереження в кеш
            if (in_array($routeKey, $cacheRoutes) && http_response_code() === 200)
                file_put_contents($cachePath, $html);

            return $html;
        }
        $core->router->error(404);
        return '';
    }


    public function display()
    {
        echo $this->getHTML();
    }

}