<?php

namespace MattyG\Framework\Core;

use \Aura\Sql_Query\QueryFactory as QueryFactory;

class DB
{
    const DB_TYPE_MYSQL = "mysql";
    const DB_TYPE_PGSQL = "pgsql";
    const DB_TYPE_SQLITE = "sqlite";
    const DB_TYPE_SQLSRV = "sqlsrv";

    /**
     * @var PDO
     */
    protected $db = null;

    /**
     * @var bool
     */
    protected $transactionActive = false;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     */
    public function __construct($dsn, $username = null, $password = null, array $driverOptions = array())
    {
        try {
            $this->db = new \PDO($dsn, $username, $password, $driverOptions);
            $this->checkDSN($dsn);
            return $this;
        } catch (PDOException $e) {
            throw new Exception("Unable to connect to the database with the details supplied.");
        }
    }

    /**
     * Parse the DSN supplied to the constructor and perform any necessary
     * tasks based on what's found.
     * At the moment, the only thing that happens is initialising an Aura
     * SQL_Query Query Factory object for those database drivers that can use
     * it.
     *
     * @param string $dsn
     */
    protected function checkDSN($dsn)
    {
        $driver = substr($dsn, 0, 6);
        switch ($driver)
        {
            case "mysql:":
                $this->queryFactory = new QueryFactory(self::DB_TYPE_MYSQL, false);
                break;
            case "pgsql:":
                $this->queryFactory = new QueryFactory(self::DB_TYPE_PGSQL, false);
                break;
            case "sqlite":
                $this->queryFactory = new QueryFactory(self::DB_TYPE_SQLITE, false);
                break;
            case "sqlsrv":
                $this->queryFactory = new QueryFactory(self::DB_TYPE_SQLSRV, false);
                break;
        }
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
     * @return PDOStatement
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
     * @return \Aura\Sql_Query\Common\SelectInterface
     */
    public function newSelectQuery()
    {
        return $this->queryFactory->newSelect();
    }

    /**
     * @return \Aura\Sql_Query\Common\InsertInterface
     */
    public function newInsertQuery()
    {
        return $this->queryFactory->newInsert();
    }

    /**
     * @return \Aura\Sql_Query\Common\UpdateInterface
     */
    public function newUpdateQuery()
    {
        return $this->queryFactory->newUpdate();
    }

    /**
     * @return \Aura\Sql_Query\Common\DeleteInterface
     */
    public function newDeleteQuery()
    {
        return $this->queryFactory->newDelete();
    }
}
