<?php

namespace app\database;

use app\config\Config;
use Exception;
use mysqli;
use mysqli_stmt;

class Database
{
    private static $instance;
    private $mysqli;
    private $options = ['db_charset' => 'utf8'];
    private $connect_error;
    private $init_start_time;
    private $reconnect_time = 60;
    private $prefix;

    public function __construct($options = [])
    {
        $this->setOptions($options ? $options : Config::getConfigData());
        $this->connect();
    }

    private function connect(): bool
    {
        mysqli_report(MYSQLI_REPORT_STRICT);
        try {
            $this->mysqli = new mysqli($this->options['db_host'], $this->options['db_user'], $this->options['db_pass'], $this->options['db_base']);
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
        $this->prefix = $this->options['db_prefix'];
        $this->init_start_time = time();
        return true;
    }

    /**
     * Выполняет запрос к базе
     * @param string $sql Строка запроса
     * @param array|string $params Аргументы запроса, которые будут переданы в prepare
     * @param boolean $quiet В случае ошибки запроса отдавать false, а не "умирать"
     * @return boolean
     */
    public function query(string $sql, $params = false): bool
    {
        if (!$this->ready()) {
            return false;
        }
        $sql = $this->replacePrefix($sql);
        if ($params) {
            if (!is_array($params)) {
                $params = [$params];
            }
            $stmt = $this->prepare($sql, $params);
            if ($stmt === null) {
                return false;
            }
            $result = $this->execute($stmt);
            $this->closeStatement($stmt);
            return $result;
        }
        if (PHP_SAPI === 'cli' && (time() - $this->init_start_time) >= $this->reconnect_time) {
            $this->reconnect();
        }
        $result = $this->mysqli->query($sql);
        if (!$this->mysqli->errno) {
            return $result;
        }
        return false;
    }

    public function ready(): bool
    {
        return $this->connect_error === false;
    }

    public function replacePrefix($sql)
    {
        return str_replace('{#}', $this->prefix, $sql);
    }

    private function prepare(string $sql, array $params): ?mysqli_stmt
    {
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            return null;
        }
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
        return $stmt;
    }

    private function execute(mysqli_stmt $stmt): bool
    {
        $result = $stmt->execute();
        $stmt->store_result();
        return $result;
    }

    private function closeStatement(mysqli_stmt $stmt): void
    {
        $stmt->close();
    }

    public function reconnect($is_force = false): bool
    {
        if ($is_force || !$this->mysqli->ping()) {
            $this->mysqli->close();
            return $this->connect();
        }
        return true;
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __destruct()
    {
        if ($this->ready()) {
            $this->mysqli->close();
        }
    }

    public function connectError()
    {
        return $this->connect_error;
    }

    public function getMysqli()
    {
        return $this->mysqli;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Выполняет запрос INSERT
     *
     * @param string $table
     * @param array $data
     * @param bool $ignore
     * @return bool|int
     */
    public function insert(string $table, array $data, bool $ignore = false)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        $params = [];
        $fieldsAndPlaceholders = $this->getFieldsAndParams($data, $params, $update_fields);
        $table = $this->replacePrefix($table);
        $sql = "INSERT " . ($ignore ? 'IGNORE ' : '') . "INTO {$table} ({$fieldsAndPlaceholders['fields']})\nVALUES ({$fieldsAndPlaceholders['placeholders']})";
        $stmt = $this->prepare($sql, $params);
        if ($stmt !== null) {
            $result = $this->execute($stmt);
            if ($result) {
                return $this->mysqli->insert_id;
            } else {
                throw new Exception("Query execution failed");
            }
        }
        return false;
    }

    /**
     * Получает строку с перечислением полей для запросов INSERT и UPDATE
     *
     * @param array $data
     * @param array $params
     * @param array|null $update_fields
     * @return array
     */
    private function getFieldsAndParams(array $data, array &$params, array &$update_fields = null): array
    {
        $fields = $placeholders = [];
        foreach ($data as $field => $value) {
            $fields[] = "`$field`";
            $placeholders[] = '?';
            $params[] = $value;
            if ($update_fields !== null) {
                $update_fields[] = "`{$field}` = ?";
            }
        }
        return ['fields' => implode(', ', $fields), 'placeholders' => implode(', ', $placeholders),];
    }

    /**
     * Выполняет запрос UPDATE
     *
     * @param string $table
     * @param string $where
     * @param array $data
     * @return bool
     */
    public function update(string $table, string $where, array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $params = [];
        $fieldsAndPlaceholders = $this->getFieldsAndParams($data, $params);
        $table = $this->replacePrefix($table);
        $sql = "UPDATE {$table} SET {$fieldsAndPlaceholders['fields']} WHERE {$where}";
        return $this->query($sql, $params);
    }

    /**
     * Выполняет запрос DELETE
     * @param string $table_name Таблица
     * @param string $where Критерии запроса
     * @return boolean
     */
    public function delete(string $table, string $where): bool
    {
        $where = str_replace('i.', '', $where);
        $table = $this->replacePrefix($table);
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->prepare($sql);
        if ($stmt !== null) {
            return $this->execute($stmt);
        }
        return false;
    }
}
