<?php

namespace controllers;

use models\HTTPResponse;
use repository\PersonsRepository;

class PersonsController extends BaseController
{
    private $repository;

    public function __construct() {
        parent::__construct();
        $this->repository = new PersonsRepository();
    }

    public function postAction($path, $params) {
        if (!$this->checkParam($params['name'])) {
            HTTPResponse::send(400, 'Parameter "name" not specified');
        }

        $userId = $this->user->getId();
        $res = $this->repository->find(array(
            'name' => $params['name'],
            'user_id' => $userId
        ));

        if (count($res) > 0) {
            HTTPResponse::send(400, 'Person already exists');
        }

        $data = array(
            'name' => $params['name'],
            'user_id' => $userId
        );

        $res = $this->repository->add($data);

        return true;
    }

    public function getAction($path, $params) {
        if ($this->user->hasPrivilege('FULL_ACCESS')) {
            $res = $this->repository->getAll();
        } else {
            $res = $this->repository->find(array('user_id' => $this->user->getId()));
        }

        return $res;
    }

    public function deleteAction($path, $params) {
        if (!isset($path[1]) || !is_numeric($path[1])) {
            HTTPResponse::send(400, 'Identifier not specified');
        }

        if (!$this->user->hasPrivilege('FULL_ACCESS')) {
            $res = $this->repository->find(array('id' => $path[1], 'user_id' => $this->user->getId()));

            if (count($res) == 0) {
                HTTPResponse::send(400, 'Record not exists');
            }
        }

        $this->repository->remove($path[1]);

        return true;
    }
}
