<?php

namespace controllers;

use repository;
use models\HTTPResponse;

class KeywordsController extends BaseController
{
    private $repository;
    private $repPersons;

    public function __construct() {
        parent::__construct();
        $this->repository = new repository\KeywordsRepository();
        $this->repPersons = new repository\PersonsRepository();
    }

    public function postAction($path, $params) {
        if (!$this->checkParam($params['name'])) {
            HTTPResponse::send(400, 'Parameter "name" not specified');
        }

        if (!$this->checkParam($params['person_id'])) {
            HTTPResponse::send(400, 'Parameter "person_id" not specified');
        }
        
        $user_id = $this->user->getId();
        if ($this->user->hasPrivilege('FULL_ACCESS')) {
            $res = $this->repPersons->get($params['person_id']);
        } else {
            $res = $this->repPersons->find(array('id' => $params['person_id'], 'user_id' => $user_id));
        }

        if (count($res) == 0) {
            HTTPResponse::send(400, 'Person not found');
            return false;
        }

        $data = array(
            'name' => $params['name'],
            'person_id' => $params['person_id'],
        );

        $res = $this->repository->add($data);
        if (!$res) {
            HTTPResponse::send(400, 'Keyword already exists');
        }

        return true;
    }

    public function getAction($path, $params) {
        $userId = $this->user->getId();
        if ($this->user->hasPrivilege('FULL_ACCESS')) {
            if (isset($params['person_id'])) {
                $persons = $this->repository->find(array('person_id' => $params['person_id']));
            } else {
                $persons = $this->repository->getAll();
            }
        } else {
            if (isset($params['person_id'])) {
                $persons = $this->repPersons->find(array('id' => $params['person_id'], 'user_id' => $userId));
            } else {
                $persons = $this->repPersons->find(array('user_id' => $userId));
            }
        }

        if (count($persons) == 0) {
            HTTPResponse::send(400, 'Person not found');
        }

        $keywords = $this->repository->getAll();

        $res = array();
        if (count($keywords) > 0) {
            foreach ($keywords as $keyword) {
                foreach ($persons as $person) {
                    if ($person['id'] == $keyword['person_id']) {
                        $res[] = $keyword;
                    }
                }
            }
        }

        return $res;
    }

    public function deleteAction($path, $params) {
        if (!isset($path[1]) || !is_numeric($path[1])) {
            HTTPResponse::send(400, 'Identifier not specified');
        }

        if (!$this->user->hasPrivilege('FULL_ACCESS')) {
            $keywords = $this->repository->get($path[1]);

            if (count($keywords) == 0) {
                HTTPResponse::send(400, 'Record not exists');
            }

            $keyword = $keywords[0];
            $persons = $this->repPersons->find(array('id' => $keyword['person_id'], 'user_id' => $this->user->getId()));

            if (count($persons) == 0) {
                HTTPResponse::send(400, 'Record not exists');
            }
        }

        $this->repository->remove($path[1]);

        return true;
    }
}
