<?php

namespace app\config;

class Config
{
    public static function getConfigData()
    {
        return [
            'root' => '/',
            'host' => 'http://beejee.loc',
            'upload_root' => '/upload/',
            'db_host' => 'localhost',
            'db_base' => 'beejee_tasks',
            'db_user' => 'root',
            'db_pass' => '',
            'db_prefix' => 'bje_',
            'db_engine' => 'InnoDB',
            'db_charset' => 'utf8',
            'clear_sql_mode' => 1,
            'debug' => true,
        ];
    }
}