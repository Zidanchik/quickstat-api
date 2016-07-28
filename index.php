<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);

require_once 'config.php';
require_once 'autoloader.php';

use models\HTTPResponse;

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');

$parsedUrl = parse_url($_SERVER['REQUEST_URI']);
if (isset($parsedUrl['query']) && $parsedUrl['query'] != '') {
    parse_str($parsedUrl['query'], $query);
    $_GET = array_merge($_GET, $query);
}

if (!isset($_SERVER['PATH_INFO'])) {
    $dir = basename(__DIR__);
    $path = parse_url($_SERVER['REQUEST_URI']);
    $path = $path['path'];
    $_SERVER['PATH_INFO'] = substr($path, strpos($path, $dir) + strlen($dir));
}

// создаем объект запроса
$request = models\Request::getInstance();
if (!$request->isValid()) {
    HTTPResponse::send(400);
}

// проверяем наличие контроллера
$path = $request->getPath();
$controllerName = 'controllers\\' . ucfirst($path[0]) . 'Controller';
if (!class_exists($controllerName)) {
    HTTPResponse::send(404, 'Object "' . $path[0] . '" Not Found');
}

// создаем котроллер
$controller = new $controllerName();

// получаем пользователя
$user = models\User::getInstance();

// проверяем необходимость авторизации для контроллера
if ($controller->isNeedAuth() && !$user->isAuthorized()) {
    HTTPResponse::send(401);
}

// выполняем действие
$actionName = $request->getMethod() . 'Action';
$result = $controller->$actionName($request->getPath(), $request->getParams());

// проверяем результат
if ($result === false) die;

// отдаем данные в формате JSON, если они есть
if ($result !== true) {
    header('Content-Type: application/json');
    echo json_encode($result);
}
