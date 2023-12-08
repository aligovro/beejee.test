<?php

namespace config;

class Config
{
    public static function getConfigData()
    {
        $randomAesKey = bin2hex(random_bytes(32));
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
            'aes_key' => $randomAesKey,
        ];
    }
}