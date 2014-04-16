<?php

$app = include(__DIR__ . "/bootstrap.php");
use \MattyG\Framework\Core\DB as DB;

$dbConfig = $app->getConfig()->getConfig("db");
if ($dbConfig["active"] === false) {
    return array();
}
if ($dbConfig["adapter"] === DB::DB_TYPE_MYSQL) {
    $dbConfig["charset"] = "utf8";
}

return array(
    "paths" => array(
        "migrations" => "db",
    ),
    "environments" => array(
        "default_migration_table" => "phinxlog",
        "default_database" => "framework",
        "framework" => $dbConfig,
    ),
);

