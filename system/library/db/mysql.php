<?php
namespace DB;

final class MySQL {
    private $connection;

    public function __construct($hostname, $username, $password, $database, $port = '3306') {
        $this->connection = new \mysqli($hostname, $username, $password, $database, $port);

        if ($this->connection->connect_error) {
            trigger_error('Error: Could not make a database link using ' . $username . '@' . $hostname);
            exit();
        }

        $this->connection->set_charset("utf8");
    }

    public function query($sql) {
        $result = $this->connection->query($sql);

        if ($result) {
            if ($result instanceof \mysqli_result) {
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free_result();

                $query = new \stdClass();
                $query->row = isset($data[0]) ? $data[0] : array();
                $query->rows = $data;
                $query->num_rows = count($data);

                return $query;
            } else {
                return true;
            }
        } else {
            throw new \Exception('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno . '<br />' . $sql);
        }
    }

    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }

    public function countAffected() {
        return $this->connection->affected_rows;
    }

    public function getLastId() {
        return $this->connection->insert_id;
    }

    public function isConnected() {
        return $this->connection->ping();
    }

    public function __destruct() {
        $this->connection->close();
    }
}
