<?php

namespace models;

use repository\UsersRepository;

class User
{
    private static $instance;

    private $id;
    private $login;
    private $repUsers;
    private $authorized = false;
    private $privileges = array();

    private function __construct() {
        if (!isset($_COOKIE['token'])) return;

        $this->repUsers = new UsersRepository();

        $res = $this->repUsers->find(array('token' => $_COOKIE['token']));
        if (count($res) == 0) return;

        $this->authorized = true;
        $this->id = $res[0]['id'];
        $this->login = $res[0]['login'];

        if ($res[0]['role'] == 1) {
            $this->privileges[] = 'FULL_ACCESS';
        }
    }

    protected function __clone() {}

    static public function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getId() {
        return $this->id;
    }

    public function isAuthorized() {
        return $this->authorized;
    }

    public function hasPrivilege($privilege) {
        return in_array($privilege, $this->privileges);
    }
}
