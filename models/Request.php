<?php

namespace models;

class Request
{
    private static $instance;

    private $path;
    private $params = array();
    private $method;
    private $valid = false;

    protected function __clone() {}

    static public function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->path = explode('/', $_SERVER['PATH_INFO']);
        $this->path = array_filter($this->path);
        $this->path = array_values($this->path);

        // если путь пустой, значит некорректный запрос
        if (count($this->path) == 0) return;

        // если запрос PUT, парсим его в массив $_PUT
        $_PUT = array();
        if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
            parse_str(file_get_contents('php://input'), $_PUT);
        }

        // сохраняем тип действия
        $this->method = $_SERVER['REQUEST_METHOD'];

        // если клиент не поддерживает запросы PUT и DELETE,
        // имитируем через запрос POST с полем _method
        if ($this->method == 'POST' && isset($_POST['_method'])) {
            $this->method = $_POST['_method'];
        }
        $this->method = strtolower($this->method);

        // объединяем параметры из масивов $_GET, $_PUT и $_POST
        $this->params = array_change_key_case(array_merge($_POST, $_PUT, $_GET), CASE_LOWER);

        $this->valid = true;
    }

    public function getPath() {
        return $this->path;
    }

    public function getMethod() {
        return $this->method;
    }

    public function getParams() {
        return $this->params;
    }

    public function isValid() {
        return $this->valid;
    }
}
