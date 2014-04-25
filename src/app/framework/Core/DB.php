<?php

namespace MattyG\Framework\Core;

use \Aura\Sql_Query\QueryFactory as QueryFactory;
use \Aura\Sql_Schema\AbstractSchema as SchemaFactory;
use \Aura\Sql_Schema\SchemaInterface as SchemaFactoryInterface;
use \Aura\Sql_Schema\ColumnFactory as SchemaColumnFactory;

use \PDO;
use \PDOException;

class DB implements SchemaFactoryInterface
{
    const DB_TYPE_MYSQL = "mysql";
    const DB_TYPE_PGSQL = "pgsql";
    const DB_TYPE_SQLITE = "sqlite";
    const DB_TYPE_SQLSRV = "sqlsrv";
    const DB_TYPE_SQLSRV_SYBASE = "sybase";
    const DB_TYPE_SQLSRV_MSSQL = "mssql";
    const DB_TYPE_SQLSRV_DBLIB = "dblib";

    const DB_CACHE_PREFIX = "core_db";

    /**
     * @var PDO
     */
    protected $db = null;

    /**
     * @var bool
     */
    protected $transactionActive = false;

    /**
     * @var \Aura\Sql_Query\QueryFactory
     */
    protected $queryFactory;

    /**
     * @var \Aura\Sql_Schema\AbstractSchema
     */
    protected $schemaFactory;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     * @throws \Exception
     */
    public function __construct($dsn, $username = null, $password = null, array $driverOptions = array())
    {
        try {
            $this->db = new PDO($dsn, $username, $password, $driverOptions);
            $this->checkDsn($dsn);
            return $this;
        } catch (PDOException $e) {
            throw new \Exception("Unable to connect to the database with the details supplied.");
        }
    }

    /**
     * Check which database driver is being used, and perform actions based on
     * said driver.
     *
     * @param string $dsn
     * @return DB
     */
    protected function checkDsn($dsn)
    {
        $driver = substr($dsn, 0, 6);
        switch ($driver)
        {
            case self::DB_TYPE_MYSQL . ":":
                $this->setQueryFactory(new QueryFactory(self::DB_TYPE_MYSQL, false));
                $this->setSchemaFactory(new \Aura\Sql_Schema\MysqlSchema($this->db, new SchemaColumnFactory()));
                break;
            case self::DB_TYPE_PGSQL . ":":
                $this->setQueryFactory(new QueryFactory(self::DB_TYPE_PGSQL, false));
                $this->setSchemaFactory(new \Aura\Sql_Schema\PgsqlSchema($this->db, new SchemaColumnFactory()));
                break;
            case self::DB_TYPE_SQLITE:
                $this->setQueryFactory(new QueryFactory(self::DB_TYPE_SQLITE, false));
                $this->setSchemaFactory(new \Aura\Sql_Schema\SqliteSchema($this->db, new SchemaColumnFactory()));
                break;
            case self::DB_TYPE_SQLSRV_SYBASE:
            case self::DB_TYPE_SQLSRV_MSSQL . ":":
            case self::DB_TYPE_SQLSRV_DBLIB . ":":
                $this->setQueryFactory(new QueryFactory(self::DB_TYPE_SQLSRV, false));
                $this->setSchemaFactory(new \Aura\Sql_Schema\SqlsrvSchema($this->db, new SchemaColumnFactory()));
                break;
        }
        return $this;
    }

    /**
     * @param \Aura\Sql_Query\QueryFactory
     * @return DB
     */
    public function setQueryFactory(QueryFactory $factory)
    {
        $this->queryFactory = $factory;
        return $this;
    }

    /**
     * @param \Aura\Sql_Schema\AbstractSchema $factory
     * @return DB
     */
    public function setSchemaFactory(SchemaFactory $factory)
    {
        $this->schemaFactory = $factory;
        return $this;
    }

    /**
     * @param Cache $cache
     * @return DB
     */
    public function setCache(Cache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @param string $type
     * @param string $dbname
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     * @return DB|null
     */
    public static function loader($type, $dbname, $hostname, $username, $password, array $driverOptions = array())
    {
        switch ($type)
        {
            case self::DB_TYPE_MYSQL:
                return self::loaderMysql($dbname, $hostname, $username, $password, $driverOptions);
            default:
                return null;
        }
    }

    /**
     * @param string $dbname
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     * @return DB
     */
    public static function loaderMysql($dbname, $hostname, $username, $password, array $driverOptions = array())
    {
        return new DB("mysql:dbname=" . $dbname . ";host=" . $hostname . ";charset=utf8", $username, $password, $driverOptions);
    }

    /**
     * Start a transaction (if one doesn't already exist).
     * Returns true if the transaction was successfully started, or if a
     * transaction already existed. Returns false if the transaction could not
     * be successfully started.
     *
     * @return bool
     */
    public function startTransaction()
    {
        if ($this->transactionActive === true) {
            return true;
        } else {
            try {
                $this->db->beginTransaction();
                $this->transactionActive = true;
            } catch (PDOException $e) {
                return false;
            }
            return true;
        }
    }

    /**
     * Commits a transaction, or rolls back if the transaction cannot be
     * committed.
     * Returns true if the transaction was committed successfully, false if it
     * wasn't, or null if there was no transaction active.
     *
     * @return bool|null
     */
    public function finishTransaction()
    {
        if ($this->transactionActive === true) {
            try {
                $this->db->commit();
                $success = true;
            } catch (PDOException $e) {
                $this->db->rollBack();
                $success = false;
            }
            return $success;
        } else {
            return null;
        }
    }

    /**
     * Rolls back a transaction without committing any changes made since
     * the transaction commenced.
     * Returns true if the transaction was rolled back successfully, false if
     * it wasn't, or null if there was no transaction active.
     *
     * @return bool|null
     */
    public function cancelTransaction()
    {
        if ($this->transactionActive === true) {
            try {
                $this->db->rollBack();
                $success = true;
            } catch (PDOException $e) {
                $success = false;
            }
            return $success;
        } else {
            return null;
        }
    }

    /**
     * Creates a new PDO prepared statement with the supplied query.
     * Returns the PDOStatement object if it was successfully created, or null
     * if it wasn't.
     *
     * @param string $query
     * @return \PDOStatement
     */
    public function newStatement($query)
    {
        try {
            $statement = $this->db->prepare($query);
        } catch (PDOException $e) {
            $statement = null;
        }
        return $statement;
    }

    /**
     * Calls lastInsertId() on the contained PDO object to retrieve the value
     * of the primary key field in the last row to be inserted into the
     * database.
     *
     * @return int|string
     */
    public function lastInsertId()
    {
        try {
            $id = $this->db->lastInsertId();
        } catch (PDOException $e) {
            $id = null;
        }
        return $id;
    }

    /**
     * Returns a new Select query builder object.
     *
     * @return \Aura\Sql_Query\Common\SelectInterface
     */
    public function newSelectQuery()
    {
        return $this->queryFactory->newSelect();
    }

    /**
     * Returns a new Insert query builder object.
     *
     * @return \Aura\Sql_Query\Common\InsertInterface
     */
    public function newInsertQuery()
    {
        return $this->queryFactory->newInsert();
    }

    /**
     * Returns a new Update query builder object.
     *
     * @return \Aura\Sql_Query\Common\UpdateInterface
     */
    public function newUpdateQuery()
    {
        return $this->queryFactory->newUpdate();
    }

    /**
     * Returns a new Delete query builder object.
     *
     * @return \Aura\Sql_Query\Common\DeleteInterface
     */
    public function newDeleteQuery()
    {
        return $this->queryFactory->newDelete();
    }

    /**
     * Saves the result of a query into the cache.
     *
     * @param string $query
     * @param mixed $result
     * @return void
     */
    public function saveQueryCache($query, $result)
    {
        if (!$this->cache) {
            return;
        }
        $cacheId = self::DB_CACHE_PREFIX . "_query_" . sha1($query);
        $this->cache->saveData($cacheId, $result, time() + 3600);
    }

    /**
     * Retrieves the result of a query from the cache.
     *
     * @param string $query
     * @return mixed
     */
    public function getQueryCache($query)
    {
        if (!$this->cache) {
            return null;
        }
        $cacheId = self::DB_CACHE_PREFIX . "_query_" . sha1($query);
        return $this->cache->loadData($cacheId, null);
    }

    /**
     * Proxy for the schema factory's fetchTableList() function.
     *
     * @param string $schema
     * @return array
     */
    public function fetchTableList($schema = null)
    {
        return $this->schemaFactory->fetchTableList($schema);
    }

    /**
     * Proxy for the schema factory's fetchTableCols() function.
     *
     * @param string $spec
     * @return array
     */
    public function fetchTableCols($spec)
    {
        return $this->schemaFactory->fetchTableCols($spec);
    }

    /**
     * Proxy for the schema factory's getColumnFactory() function.
     *
     * @return \Aura\Sql_Schema\ColumnFactory
     */
    public function getColumnFactory()
    {
        return $this->schemaFactory->getColumnFactory();
    }
}
