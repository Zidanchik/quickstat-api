<?php

namespace controllers;

use repository\UsersRepository;
use models\HTTPResponse;

class AuthController extends BaseController
{
    private $repository;

    public function __construct() {
        parent::__construct(false);
        $this->repository = new UsersRepository();
    }

    public function postAction($path, $params) {
        if (!isset($path[1])) return false;

        switch ($path[1]) {
            case 'register':
                return $this->register($params);
            case 'logout':
                return $this->logout();
            case 'login':
                return $this->login($params);
        }

        return false;
    }

    private function register($params) {
        if ($this->user->isAuthorized() && !$this->user->hasPrivilege('FULL_ACCESS')) {
            HTTPResponse::send(400, 'Already authorized, logout first');
        }

        $this->checkParams($params);

        if (isset($params['role']) && $params['role'] == '1' && !$this->user->hasPrivilege('FULL_ACCESS')) {
            HTTPResponse::send(403, 'Registration of a new administrator allowed by another administrator only');
        }

        if (!$this->checkParam($params['role'])) {
            $params['role'] = '0';
        }

        $pass = $this->getHash($params['password']);
		$data = array(
            'pass' => $pass,
            'role' => $params['role'],
			'login' => $params['login']
		);

        $res = $this->repository->add($data);
        if (!$res) {
            HTTPResponse::send(400, 'User already exists');
        }

        return true;
    }

    private function logout() {
        if (!$this->user->isAuthorized()) {
            HTTPResponse::send(401);
        }

        $res = $this->repository->change($this->user->getId(), array('token' => NULL));

        setcookie('token', NULL, -1, '/', $_SERVER['HTTP_HOST']);
    
        return true;
    }

    private function login($params) {
        if ($this->user->isAuthorized()) {
            HTTPResponse::send(400, 'Already authorized, logout first');
        }

        $this->checkParams($params);

        $res = $this->repository->find(array('login' => $params['login']));

        if (count($res) == 0) {
            HTTPResponse::send(400, 'User not found');
        }

        $res = $res[0];
        $password = $this->getHash($params['password']);

        if ($password != $res['pass']) {
            HTTPResponse::send(400, 'Invalid password');
        }

        $token = $this->getToken($res['login']);
        $res = $this->repository->change($res['id'], array('token' => $token));

        setcookie('token', $token, time() + DAY_IN_SECONDS * 30, '/', $_SERVER['HTTP_HOST']);
        
        return $token;
    }

    private function checkParams($params) {
        if (!$this->checkParam($params['login'])) {
            HTTPResponse::send(400, 'Login not specified');
        }

        if (!$this->checkParam($params['password'])) {
            HTTPResponse::send(400, 'Password not specified');
        }

        if (!preg_match(LOGIN_REGEX, $params['login'])) {
            HTTPResponse::send(400, 'Invalid login');
        }

        if (!preg_match(PASSW_REGEX, $params['password'])) {
            HTTPResponse::send(400, 'Invalid password');
        }

        return true;
    }

    private function getHash($str) {
        $hash = hash('sha256', $str);
        $hash = hash('sha256', $hash . 'o3w47ty8');
        return $hash;
    }

    private function getToken($login) {
        $token = $login . ':' . time();
        return $this->getHash($token);
    }
}
