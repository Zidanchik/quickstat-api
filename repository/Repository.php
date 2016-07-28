<?php

namespace repository;

use models\MySQL;
use models\HTTPResponse;

class Repository implements IRepository
{
    const NO_ERROR = 0;
    const ERROR_DB_ERROR = 1;
    const ERROR_DUPLICATED = 2;
    const ERROR_NOT_EXISTS = 3;

    protected $db;
    protected $key;
    protected $table;

    public $error;

    public function __construct($table, $key = 'id') {
        $this->db = MySQL::getInstance();
        $this->table = $table;
        $this->key = $key;
    }

    public function add($data) {
        $res = $this->db->insert($this->table, $data);
        if (!$res) {
            $errorCode = $this->db->getErrorCode();
            $errorText = $this->db->getErrorText();

            $this->db->resetAutoIncrement($this->table);

            if ($errorCode == MySQL::ER_DUP_ENTRY) {
                return false;
            } else {
                HTTPResponse::send(500, 'Database error');
            }
        }

        return $res;
    }

    public function get($id) {
        return $this->find(array($this->key => $id));
    }

    public function getAll() {
        return $this->find(array('1' => '1'));
    }

    public function change($id, $data) {
        $res = $this->db->update($this->table, $data, "{$this->key}=$id");
        if (!$res) {
            if ($res === 0) {
                HTTPResponse::send(400, 'Record not exists');
            } else {
                HTTPResponse::send(500, 'Database error');
            }
        }

        return $res;
    }

    public function remove($id) {
        $res = $this->db->delete($this->table, "{$this->key}=$id");
        if (!$res) {
            if ($res === 0) {
                HTTPResponse::send(400, 'Record not exists');
            } else {
                HTTPResponse::send(500, 'Database error');
            }

            return false;
        }

        return $res;
    }

    public function find($filter) {
        $where = array();

        foreach ($filter as $key => $value) {
            if ($value == NULL) {
                $where[] = "$key=NULL";
            } else {
                $where[] = "$key='$value'";
            }
        }

        $where = implode(' AND ', $where);

        $res = $this->db->select("SELECT * FROM {$this->table} WHERE $where");
        if (is_array($res)) return $res;

        HTTPResponse::send(500, 'Database error');
    }
}
