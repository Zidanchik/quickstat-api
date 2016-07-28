<?php

namespace controllers;

use repository\UsersRepository;
use models\HTTPResponse;

class UsersController extends BaseController
{
    private $repository;

    public function __construct() {
        parent::__construct();
        $this->repository = new UsersRepository();
    }

    public function getAction($path, $params) {
        if (!$this->user->hasPrivilege('FULL_ACCESS')) {
            HTTPResponse::send(403, 'Allowed for administrator only');
        }

        $res = $this->repository->getAll();

        foreach ($res as $key => $value) {
            $res[$key]['pass'] = NULL;
            $res[$key]['token'] = NULL;
            unset($res[$key]['pass']);
            unset($res[$key]['token']);
        }

        return $res;
    }

    public function deleteAction($path, $params) {
        if (!$this->user->hasPrivilege('FULL_ACCESS')) {
            HTTPResponse::send(403, 'Allowed for administrator only');
        }

        if (!isset($path[1]) || !is_numeric($path[1])) {
            HTTPResponse::send(400, 'Identifier not specified');
        }

        $res = $this->repository->remove($path[1]);

        return true;
    }
}
