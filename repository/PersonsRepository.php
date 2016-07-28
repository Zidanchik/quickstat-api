<?php

namespace repository;

class PersonsRepository extends Repository
{
    public function __construct() {
        parent::__construct('persons');
    }
}
