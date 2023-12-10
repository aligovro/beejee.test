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
    private $init_start_time;
    private $connect_error;
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
        $this->handleOptions();
        $this->prefix = $this->options['db_prefix'];
        $this->init_start_time = time();
        return true;
    }

    private function handleOptions(): void
    {
        if (!empty($this->options['clear_sql_mode'])) {
            $this->mysqli->query("SET sql_mode=''");
        }

        if (!empty($this->options['aes_key'])) {
            $key = $this->mysqli->real_escape_string($this->options['aes_key']);
            $this->mysqli->query("SELECT @aeskey:='{$key}'");
        }
    }

    public function query(string $sql, $params = null): bool
    {
        $sql = $this->replacePrefix($sql);

        if ($params) {
            return $this->prepareAndExecute($sql, $params);
        }

        if ($this->shouldReconnect()) {
            $this->reconnect();
        }

        $result = $this->mysqli->query($sql);
        return !$this->mysqli->errno && $result;
    }

    public function replacePrefix($sql)
    {
        return str_replace('{#}', $this->prefix, $sql);
    }

    private function prepareAndExecute(string $sql, $params = null): bool
    {
        $stmt = $this->prepare($sql, $params);
        if ($stmt === null) {
            return false;
        }

        $result = $this->execute($stmt);
        $this->closeStatement($stmt);
        return $result;
    }

    private function prepare(string $sql, array $params = []): ?mysqli_stmt
    {
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            return null;
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

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

    private function shouldReconnect(): bool
    {
        return PHP_SAPI === 'cli' && (time() - $this->init_start_time) >= $this->reconnect_time;
    }

    private function reconnect($is_force = false): bool
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
        $this->mysqli->close();
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
     * @throws Exception
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
        if (empty($data) || !isset($data['id'])) {
            return false;
        }

        $params = [];
        $setStatements = [];

        foreach ($data as $key => $value) {
            if ($key !== 'id') {
                $setStatements[] = "`{$key}` = ?";
                $params[] = $value;
            }
        }

        $params[] = $data['id'];
        $table = $this->replacePrefix($table);
        $setClause = implode(', ', $setStatements);
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";

        return $this->query($sql, $params);
    }

    /**
     * Выполняет запрос SELECT
     *
     * @param string $table
     * @param array $columns
     * @param string $where
     * @param array $params
     * @return array|false
     */
    public function select(string $table, array $columns = ['*'], string $where = '', array $params = [])
    {
        if (empty($columns)) {
            $columns = ['*'];
        }
        $table = $this->replacePrefix($table);
        $selectColumns = implode(', ', $columns);
        $sql = "SELECT {$selectColumns} FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->prepare($sql, $params);
        if ($stmt !== null) {
            $result = $this->execute($stmt);
            if ($result) {
                $data = $this->fetchDataAsArray($stmt);
                $this->closeStatement($stmt);
                return $data;
            } else {
                echo "Error executing query: " . $this->mysqli->error;
            }
        } else {
            echo "Error preparing query: " . $this->mysqli->error;
        }
        return false;
    }

    private function fetchDataAsArray(mysqli_stmt $stmt): array
    {
        $result = [];
        $stmt->store_result();
        $meta = $stmt->result_metadata();
        $row = [];

        foreach ($meta->fetch_fields() as $field) {
            $row[$field->name] = null;
            $params[] = &$row[$field->name];
        }

        call_user_func_array([$stmt, 'bind_result'], $params);

        while ($stmt->fetch()) {
            $currentRow = [];

            foreach ($row as $key => $value) {
                $currentRow[$key] = $value;
            }

            $result[] = $currentRow;
        }

        return $result;
    }

    public function selectById(string $table, int $id, string $idColumn = 'id')
    {
        $table = $this->replacePrefix($table);
        $sql = "SELECT * FROM {$table} WHERE {$idColumn} = ?";
        $params = [$id];
        $stmt = $this->prepare($sql, $params);

        if ($stmt !== null) {
            $result = $this->execute($stmt);

            if ($result) {
                $data = $this->fetchDataAsArray($stmt);

                if ($data !== false && count($data) == 1) {
                    return $data[0];
                }

                $this->closeStatement($stmt);
                return $data;
            }
        }

        return false;
    }

    private function countRows(string $table)
    {
        $table = $this->replacePrefix($table);
        $sql = "SELECT COUNT(*) AS count FROM {$table}";
        $stmt = $this->prepare($sql);
        if ($stmt !== null) {
            $result = $this->execute($stmt);
            if ($result) {
                $count = $this->fetchDataAsArray($stmt)[0]['count'];
                $this->closeStatement($stmt);
                return $count;
            }
        }
        return 0;
    }

    public function selectWithPagination(string $table, array $columns = ['*'], string $where = '', array $params = [], string $orderBy = 'id DESC', ?int $page = null, int $perPage = 3)
    {
        $offset = 0;
        $limit = '';
        $paramsForSelect = array_merge($params, [$offset, $perPage]);
        if ($page !== null) {
            $offset = ($page - 1) * $perPage;
            $limit = "LIMIT ?, ?";
            $paramsForSelect = [$offset, $perPage];
        }
        $table = $this->replacePrefix($table);
        $selectColumns = implode(', ', $columns);
        $sql = "SELECT {$selectColumns} FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY {$orderBy} {$limit}";
        $stmt = $this->prepare($sql, $paramsForSelect);
        if ($stmt !== null) {
            $result = $this->execute($stmt);
            if ($result) {
                $data = $this->fetchDataAsArray($stmt);
                $this->closeStatement($stmt);
                $totalCount = $this->countRows($table);
                return ['data' => $data, 'totalCount' => $totalCount];
            }
        }
        return false;
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
