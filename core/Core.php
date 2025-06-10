<?php

namespace core;

class Core
{
    public string $defaultLayoutPath = 'views/layouts/index.php';
    public $moduleName;
    public $actionName;
    public $router;
    public $template;
    public $db;
    public Controller $controllerObject;
    public Session $session;
    private static $instance;

    private function __construct()
    {
        $this->template = new Template($this->defaultLayoutPath);
        $host = Config::get()->dbHost;
        $name = Config::get()->dbName;
        $login = Config::get()->dbLogin;
        $password = Config::get()->dbPassword;
        $this->db = new DB($host, $name, $login, $password);
        $this->session = new Session();


        // session_start();
    }

    public function run($route)
    {
        $this->router = new \core\Router($route ?? null);
        $params = $this->router->run();
        if(!empty($params))
            $this->template->setParams($params);
    }

    public function done()
    {
        $statusCode = http_response_code();
        $this->logRequest($statusCode);

        $this->template->display();

        

        $this->router->done();
    }

    public static function get()
    {
        if(empty(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function logRequest(int $statusCode)
    {
        if ($statusCode === 200) 
            return;

        $data = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'status_code' => $statusCode,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '::1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'controller' => $this->moduleName ?? '',
            'action' => $this->actionName ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];

        \models\Logs::Add($data);
    }

}