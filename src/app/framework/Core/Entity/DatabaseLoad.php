<?php

namespace MattyG\Framework\Core\Entity;

trait DatabaseLoad
{
    /**
     * @param int|string $id
     * @param string $fieldName
     * @return $this
     */
    public function load($id, $fieldName = null)
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        if ($fieldName === null) {
            $fieldName = $this->getIdFieldName();
        }
        $this->_beforeLoad($id, $fieldName);
        $this->_loadData($id, $fieldName);
        $this->_afterLoad();
        return $this;
    }

    /**
     * @param int|string $id
     * @param string $fieldName
     * @return $this
     */
    protected function _beforeLoad($id, $fieldName)
    {
        return $this;
    }

    /**
     * @param int|string $id
     * @param string $fieldName
     * @return $this
     */
    protected function _loadData($id, $fieldName)
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        if ($this->_useQueryBuilder === true) {
            list($statement, $params) = $this->_getLoadStatementBuilder($id, $fieldName);
        } else {
            list($statement, $params) = $this->_getLoadStatementSql($id, $fieldName);
        }
        /** @var $statement \PDOStatement */
        /** @var $params array */
        $statement->execute($params);
        $data = $statement->fetch(\PDO::FETCH_ASSOC);
        if ($data) {
            $this->setData($data);
			$this->newObject = false;
        }
        return $this;
    }

    /**
     * @param int|string $id
     * @param string $fieldName
     * @return \PDOStatement, array
     */
    protected function _getLoadStatementSql($id, $fieldName)
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        $query = "SELECT * FROM `{$this->_tableName}` WHERE `{$this->_tableName}`.`$fieldName` = ?";
        $statement = $this->db->newStatement($query);
        $params = array(0 => $id);
        return array($statement, $params);
    }

    /**
     * @param int|string $id
     * @param string $fieldName
     * @return \PDOStatement, array
     */
    protected function _getLoadStatementBuilder($id, $fieldName)
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        $query = $this->db->newSelectQuery();
        $query->from($this->_tableName)
            ->cols(array("*"))
            ->where($this->_tableName . "." . $fieldName . " = ?");
        $statement = $this->db->newStatement((string) $query);
        $params = array(1 => $id);
        return array($statement, $params);
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        return $this;
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        return $this->_afterLoad();
    }
}

