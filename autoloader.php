<?php

class Autoloader {
    public static function autoload($class) {
        $class = str_replace('\\', '/', $class);
        $class = $class.'.php';
        if (file_exists($class)) require_once $class;
    }
}

spl_autoload_register(array('Autoloader', 'autoload'));
