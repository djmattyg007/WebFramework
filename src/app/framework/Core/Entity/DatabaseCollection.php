<?php

namespace MattyG\Framework\Core\Entity;

// Constants really shouldn't be in traits.
// Once PHP 5.6 is released, these will be moved into
// a namespace.
// It turns out traits simply cannot have constants at all.
// This is now a super-hack until PHP 5.6 is released.
define("FILTER_FLAG_NULL", null);
define("FILTER_FLAG_AND", "and");
define("FILTER_FLAG_NOT", "not");
define("FILTER_FLAG_GT", "gt");
define("FILTER_FLAG_LT", "lt");

trait DatabaseCollection
{
    use Collection {
        Collection::clear as parentClear;
        Collection::getIterator as parentIterator;
        Collection::count as parentCount;
        Collection::getFirstItem as parentFirstItem;
        Collection::getAllIds as parentAllIds;
    }
    use Database;


    /**
     * @var string|callable
     */
    protected $object = null;

    /**
     * @var bool
     */
    protected $loaded = false;

    /**
     * @var array
     */
    protected $filters = array();

    /**
     * @var string
     */
    protected $order = null;

    /**
     * @var string
     */
    protected $direction = "asc";

    /**
     * @var int
     */
    protected $limit = null;

    /**
     * @var int
     */
    protected $offset = null;

    /**
     * @return object
     */
    public function getNewObjectInstance()
    {
        $object = $this->object;
        if (is_callable($object)) {
            return call_user_func($object);
        } else {
            return new $object();
        }
    }

    /**
     * @return $this
     */
    public function load()
    {
        if ($this->loaded === true) {
            return $this;
        }
        list($statement, $params) = $this->buildQuery();
        $statement->execute($params);
        $this->_loadData($statement->fetchAll(\PDO::FETCH_ASSOC));
        $this->loaded = true;
        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        if ($this->size !== null) {
            return $this->size;
        }
        list($statement, $params) = $this->buildSizeQuery();
        $statement->execute($params);
        $this->size = intval($statement->fetchColumn(0));
        return $this->size;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->loaded = false;
        return $this->parentClear();
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        $this->load();
        return $this->parentIterator();
    }

    /**
     * @return object|mixed
     */
    public function getFirstItem()
    {
        $this->load();
        return $this->parentFirstItem();
    }

    /**
     * @param bool $useLimit
     * @return array
     */
    public function getAllIds($useLimit = true)
    {
        $query = "SELECT `" . $this->getNewObjectInstance()->getIdFieldName() . "` FROM `{$this->_tableName}`";
        list($whereQuery, $params) = $this->buildQueryWhere();
        $query .= $whereQuery;
        $query .= $this->_buildQueryOrder();
        if ($useLimit === true) {
            $query .= $this->_buildQueryLimit();
        }
        $statement = $this->db->newStatement($query);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return \PDOStatement, array
     */
    public function buildQuery()
    {
        $query = "SELECT * FROM `{$this->_tableName}`";
        list($whereQuery, $params) = $this->buildQueryWhere();
        $query .= $whereQuery;
        $query .= $this->_buildQueryOrder();
        $query .= $this->_buildQueryLimit();
        $statement = $this->db->newStatement($query);
        return array($statement, $params);
    }

    /**
     * @return \PDOStatement, array
     */
    public function buildSizeQuery()
    {
        $query = "SELECT COUNT(*) FROM `{$this->_tableName}`";
        list($whereQuery, $params) = $this->buildQueryWhere();
        $query .= $whereQuery;
        $statement = $this->db->newStatement($query);
        return array($statement, $params);
    }

    /**
     * @return string, array
     */
    public function buildQueryWhere()
    {
        $query = "";
        $params = array();
        if ($this->filters) {
            $query .= " WHERE";
            $parts = array();
            foreach ($this->filters as $filter) {
                $parts[] = $this->_processFilter($filter, $params);
            }
            $query .= "(" . implode(") AND (", $parts) . ")";
        }
        return array($query, $params);
    }

    /**
     * @param array $filter
     * @param array $params
     * @return string
     */
    protected function _processFilter(array $filter, array &$params)
    {
        $part = " {$filter["column"]} ";
        if ($filter["value"] === null) {
            if ($filter["flag"] === FILTER_FLAG_NOT) {
                $part .= "IS NOT NULL";
            } else {
                $part .= "IS NULL";
            }
        } elseif (is_array($filter["value"])) {
            if (isset($filter["value"][0]["value"])) {
                // It's a bunch of filters to be or'd together.
                $processed = array();
                foreach ($filter["value"] as $subFilter) {
                    $processed[] = $this->_processFilter($subFilter, $params);
                }
                if ($filter["flag"] === FILTER_FLAG_AND) {
                    $join = "AND";
                } else {
                    $join = "OR";
                }
                $part = "(" . implode(") $join (", $processed) . ")";
            } else {
                if ($filter["flag"] === FILTER_FLAG_NOT) {
                    $part .= "NOT ";
                }
                $part .= "IN (" . substr(str_repeat(",?", count($filter["value"])), 1) . ")";
                $params = array_merge($params, $filter["value"]);
            }
        } elseif (is_string($filter["value"]) && strpos($filter["value"], "%")) {
            if ($filter["flag"] === FILTER_FLAG_NOT) {
                $part .= "NOT ";
            }
            $part .= "LIKE ?";
            $params = array_merge($params, $filter["value"]);
        } else {
            switch ($filter["flag"])
            {
                case FILTER_FLAG_NOT:
                    $part .= "!= ?";
                    break;
                case FILTER_FLAG_GT:
                    $part .= ">= ?";
                    break;
                case FILTER_FLAG_LT:
                    $part .= "<= ?";
                    break;
                default:
                    $part .= "= ?";
                    break;
            }
            $params = array_merge($params, $filter["value"]);
        }
        return $part;
    }

    /**
     * @return string
     */
    protected function _buildQueryOrder()
    {
        if ($this->order) {
            return " ORDER BY `{$this->order}` {$this->direction}";
        } else {
            return "";
        }
    }

    /**
     * @return string
     */
    protected function _buildQueryLimit()
    {
        if ($this->limit) {
            $query = " LIMIT {$this->limit}";
            if ($this->offset) {
                $query .= ", {$this->offset}";
            }
            return $query;
        } else {
            return "";
        }
    }

    /**
     * @param array $data
     */
    protected function _loadData(array $data)
    {
        foreach ($data as $row) {
            $object = $this->getNewObjectInstance();
            $object->setData($row)
                ->setNewObject(false)
                ->afterLoad();
            $this->addItem($object->getId(), $object);
        }
    }


    /**
     * @param string $column
     * @param array|mixed $value
     * @param bool $flag
     * @return $this
     */
    public function addFilter($column, $value, $flag = FILTER_FLAG_NULL)
    {
        $this->filters[] = array(
            "column" => (string) $column,
            "value" => $value,
            "flag" => $flag,
        );
        return $this;
    }

    /**
     * @param string $order
     * @param string $direction
     * @return $this
     */
    public function setOrder($order, $direction = "asc")
    {
        $this->order = (string) $order;
        $this->direction = $direction;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearOrder()
    {
        $this->order = null;
        $this->direction = "asc";
        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function setLimit($limit, $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearLimit()
    {
        $this->limit = null;
        $this->offset = null;
        return $this;
    }
}

