<?php

namespace MattyG\Framework\Core;

use \MattyG\Framework\Core\DB\Adapter as Adapter;
use \Aura\Sql_Query\QueryFactory as QueryFactory;

use \PDO;
use \PDOException;

class DB
{
    const DB_TYPE_MYSQL = "mysql";

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
     * @var \MattyG\Framework\Core\DB\Adapter
     */
    protected $adapter;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     */
    public function __construct($dsn, $username = null, $password = null, array $driverOptions = array())
    {
        try {
            $this->db = new PDO($dsn, $username, $password, $driverOptions);
            return $this;
        } catch (PDOException $e) {
            throw new Exception("Unable to connect to the database with the details supplied.");
        }
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
     * @param MattyG\Framework\Core\DB\Adapter
     * @return DB
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
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
        $db = null;
        switch ($type)
        {
            case self::DB_TYPE_MYSQL:
                $db = self::loaderMysql($dbname, $hostname, $username, $password, $driverOptions);
                break;
            default:
                return null;
        }

        $db->setQueryFactory(new QueryFactory($type, false));
        $db->setAdapter(Adapter::loader($type, $db));
        return $db;
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
        $this->cache->saveCache($cacheId, $result, time() + 3600);
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
        return $this->cache->loadCache($cacheId, null);
    }

    /**
     * Act as a proxy for the adapter attached to the DB object.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!$this->adapter) {
            throw new \BadMethodCallException();
        }
        if (!method_exists($this->adapter, $method)) {
            throw new \BadMethodCallException();
        }
        return call_user_func_array(array($this->adapter, $method), $args);
    }
}
