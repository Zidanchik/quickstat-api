<?php

namespace repository;

class UsersRepository extends Repository
{
    public function __construct() {
        parent::__construct('users');
    }
}
