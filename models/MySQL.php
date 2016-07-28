<?php

namespace models;

class MySQL
{
    const ER_DUP_ENTRY = 1062;

    private $db;
    private $logFile;
    private static $instance;

    private function __construct() {
        $this->db = new \mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DB);
        if ($this->db->connect_error) {
            die('Connect error (' . $this->db->connect_errno . ') ' . $this->db->connect_error);
        }
        $this->db->set_charset('utf8');
        $this->logFile = fopen('db.log', 'a');
    }

    protected function __clone() {}

    static public function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function log($msg) {
        $time = '[' . date('Y-m-d H:i:s', time()) . '] ';
        fputs($this->logFile, $time . $msg . PHP_EOL);
    }

    public function resetAutoIncrement($table) {
        $this->db->query("ALTER TABLE $table AUTO_INCREMENT=1");
    }

    public function getErrorCode() {
        return $this->db->errno;
    }

    public function getErrorText() {
        return $this->db->error;
    }

    public function insert($table, $data) {
        $columns = array();
        $values = array();

        foreach ($data as $key => $value) {
            $key = $this->db->real_escape_string($key);
            $columns[] = $key;

            if ($value == NULL) {
                $values[] = 'NULL';
            } else {
                $value = $this->db->real_escape_string($value);
                $values[] = "'$value'";
            }
        }

        $columns = implode(',', $columns);
        $values = implode(',', $values);

        $sql = "INSERT INTO $table ($columns) VALUES ($values)";
        $res = $this->db->query($sql);

        if (!$res) {
            $this->log('Insert error (' . $this->db->errno . ') ' . $this->db->error);
            return false;
        }

        return $this->db->insert_id;
    }

    public function select($sql) {
        $res = $this->db->query($sql);
        if ($this->db->errno) {
            $this->log('Select error (' . $this->db->errno . ') ' . $this->db->error);
            return false;
        }

        $data = array();
        for ($i=0; $i < $res->num_rows; $i++) { 
            $data[] = $res->fetch_assoc();
        }

        return $data;
    }

    public function update($table, $data, $where) {
        $set = array();

        foreach ($data as $key => $value) {
            $key = $this->db->real_escape_string($key);

            if ($value == NULL) {
                $set[] = "$key=NULL";
            } else {
                $value = $this->db->real_escape_string($value);
                $set[] = "$key='$value'";
            }
        }

        $set = implode(',', $set);

        $sql = "UPDATE $table SET $set WHERE $where";
        $res = $this->db->query($sql);

        if (!$res) {
            $this->log('Update error (' . $this->db->errno . ') ' . $this->db->error);
            return false;
        }

        return $this->db->affected_rows;
    }

    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        $res = $this->db->query($sql);

        if (!$res) {
            $this->log('Delete error (' . $this->db->errno . ') ' . $this->db->error);
            return false;
        }

        return $this->db->affected_rows;
    }
}
