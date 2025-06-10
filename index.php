<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ob_start();
require_once 'autoload.php';

if(isset($_GET['route']))
    $route = $_GET['route'];
else
    $route = null;

$core = \core\Core::get();
$core->run($route);
$core->done();
?>
