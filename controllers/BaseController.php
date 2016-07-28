<?php

namespace controllers;

use models;

class BaseController
{
    protected $user;
    protected $needAuth;

    public function __construct($needAuth = true) {
        $this->user = models\User::getInstance();
        $this->needAuth = $needAuth;
    }

    public function __call($name, $arguments) {
        models\HTTPResponse::set(405);
        return false;
    }

    public function isNeedAuth() {
        return $this->needAuth;
    }

    protected function checkParam(&$param) {
        if (!isset($param) || $param == '') return false;

        return true;
    }
}
