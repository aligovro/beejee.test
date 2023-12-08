<?php


use database\CreateTables;
use database\Database;

error_reporting(-1);


function autoloader($class)
{
    $class = str_replace("\\", "/", $class);
    $file = __DIR__ . "/{$class}.php";
    //die($file);

    if(file_exists($file)) {
        require_once($file);
    }
}

spl_autoload_register('autoloader');


$db = Database::getInstance();

$migration = new CreateTables($db);

$migration->run();


