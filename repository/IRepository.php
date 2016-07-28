<?php

namespace repository;

interface IRepository
{
    public function add($data);
    public function get($id);
    public function getAll();
    public function change($id, $data);
    public function remove($id);
    public function find($filter);
}
