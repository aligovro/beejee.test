<?php

namespace database;

use config\Config;
use Exception;

class Database {
    private static $instance;
    private $mysqli;
    private $options = [
        'db_charset' => 'utf8'
    ];
    private $connect_error;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct($options = []) {
        $this->setOptions($options ? $options : Config::getConfigData());
        $this->connect();
    }

    public function __destruct() {
        if ($this->ready()) {
            if (!$this->isAutocommitOn() && $this->mysqli->errno) {
                $this->rollback();
            }
            $this->mysqli->close();
        }
    }

    public function setOptions($options) {
        $this->options = array_merge($this->options, $options);
    }

    private function connect() {
        mysqli_report(MYSQLI_REPORT_STRICT);
        try {
            $this->mysqli = new \mysqli(
                $this->options['db_host'],
                $this->options['db_user'],
                $this->options['db_pass'],
                $this->options['db_base']
            );
        } catch (Exception $e) {
            $this->connect_error = $e->getMessage();
            return false;
        }
        $this->mysqli->set_charset($this->options['db_charset']);
        if (!empty($this->options['clear_sql_mode'])) {
            $this->mysqli->query("SET sql_mode=''");
        }
        if (!empty($this->options['aes_key'])) {
            $key = $this->mysqli->real_escape_string($this->options['aes_key']);
            $this->mysqli->query("SELECT @aeskey:='{$key}'");
        }
        if (!empty($this->options['debug'])) {
            // cmsDebugging::pointProcess('db', array('data' => 'Database connection'), 3);
        }
        $this->prefix = $this->options['db_prefix'];
        $this->init_start_time = time();
        return true;
    }

    public function ready() {
        return $this->connect_error === false;
    }

    public function connectError() {
        return $this->connect_error;
    }

    public function reconnect($is_force = false) {
        if ($is_force || !$this->mysqli->ping()) {
            $this->mysqli->close();
            return $this->connect();
        }
        return true;
    }

    public function getMysqli()
    {
        return $this->mysqli;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
