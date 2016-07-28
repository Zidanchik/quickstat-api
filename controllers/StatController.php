<?php

namespace controllers;

use repository;

class StatController extends BaseController
{
    private $repPersons;
    private $repPages;
    private $repRanks;
    private $repSites;

    public function __construct() {
        parent::__construct();
        $this->repPersons   = new repository\PersonsRepository();
        $this->repPages     = new repository\PagesRepository();
        $this->repRanks     = new repository\RanksRepository();
        $this->repSites     = new repository\SitesRepository();
    }

    public function getAction($path, $params) {
        if (!isset($path[1])) return false;

        switch ($path[1]) {
            case 'common':
                return $this->getCommonStats($params);
            case 'daily':
                return $this->getDailyStats($params);
        }

        return false;
    }

    private function getCommonStats($params) {
        if (!$this->checkParam($params['site_id'])) {
            HTTPResponse::send(400, 'Parameter "site_id" not specified');
        }

        $res = array();

        if ($this->user->hasPrivilege('FULL_ACCESS')) {
            $sites = $this->repSites->get($params['site_id']);
            $persons = $this->repPersons->getAll();
        } else {
            $userId = $this->user->getId();

            $sites = $this->repSites->find(array('id' => $params['site_id'], 'user_id' => $userId));
            $persons = $this->repPersons->find(array('user_id' => $userId));
        }

        $pages = $this->repPages->find(array('site_id' => $params['site_id']));
        $ranks = $this->repRanks->getAll();

        if (!count($sites) || !count($persons) || !count($pages) || !count($ranks)) {
            return $res;
        }

        foreach ($persons as $person) {
            $personRank = 0;

            foreach ($pages as $page) {
                foreach ($ranks as $rank) {
                    if ($rank['person_id'] == $person['id'] && $rank['page_id'] == $page['id']) {
                        $personRank += $rank['rank'];
                    }
                }
            }

            $res[] = array(
                'id'    => $person['id'],
                'name'  => $person['name'],
                'rank'  => $personRank
            );
        }

        return $res;
    }

    private function getDailyStats($params) {
        if (!$this->checkParam($params['site_id'])) {
            HTTPResponse::send(400, 'Parameter "site_id" not specified');
        }

        if (!$this->checkParam($params['person_id'])) {
            HTTPResponse::send(400, 'Parameter "person_id" not specified');
        }

        $res = array();

        if (!isset($params['first_date']) || !isset($params['last_date'])) {
            $params['first_date'] = date('Y-m-d', time() - DAY_IN_SECONDS * 30);
            $params['last_date'] = date('Y-m-d', time());
        }

        $params['first_date'] = strtotime($params['first_date']);
        $params['last_date'] = strtotime($params['last_date']);

        if ($params['first_date'] > $params['last_date']) {
            return false;
        }

        if ($this->user->hasPrivilege('FULL_ACCESS')) {
            $sites = $this->repSites->get($params['site_id']);
            $persons = $this->repPersons->get($params['person_id']);
        } else {
            $userId = $this->user->getId();

            $sites = $this->repSites->find(array('id' => $params['site_id'], 'user_id' => $userId));
            $persons = $this->repPersons->find(array('id' => $params['person_id'], 'user_id' => $userId));
        }

        $pages = $this->repPages->find(array('site_id' => $params['site_id']));
        $ranks = $this->repRanks->find(array('person_id' => $params['person_id']));

        if (!count($sites) || !count($persons) || !count($pages) || !count($ranks)) {
            return $res;
        }

        $res = array('pagesByDays' => array(), 'totalPages' => 0);

        $date = $params['first_date'];
        do {
            $newPages = 0;

            foreach ($ranks as $rank) {
                foreach ($pages as $page) {
                    $pageDate = strtotime($page['found_date_time']);
                    $pageDate = date('Y-m-d', $pageDate);
                    $pageDate = strtotime($pageDate);
                    if ($pageDate == $date && $rank['page_id'] == $page['id']) {
                        $newPages++;
                    }
                }
            }

            $res['pagesByDays'][] = array(
                'date' => date('d-m-Y', $date),
                'pages' => $newPages
            );
            $res['totalPages'] += $newPages;

            $date += DAY_IN_SECONDS;
        } while ($date < $params['last_date']);

        return $res;
    }
}
