<?php
require "../bootstrap.php";
use Src\Controller\PacienteController;
use Src\Controller\ExameController;
use Src\Controller\PedidoController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

$pk = null;
if (isset($uri[2])) {
    $pk = (int) $uri[2];
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

switch ($obj) {
    case 'paciente':
        $controller = new PacienteController($dbConnection, $requestMethod, $pk);
        break;

    case 'exame':
        $controller = new ExameController($dbConnection, $requestMethod, $pk);
        break;

    case 'pedido':
        $controller = new PedidoController($dbConnection, $requestMethod, $pk);
        break;
    
    default:
        $controller = null;
        break;
}

if ($controller) {
    $controller->processRequest();
}
