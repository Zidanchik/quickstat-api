<?php

namespace repository;

class RanksRepository extends Repository
{
    public function __construct() {
        parent::__construct('person_page_rank');
    }

    public function get($id) {
        return false;
    }

    public function remove($id) {
        return false;
    }
}
