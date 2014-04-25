<?php

namespace MattyG\Framework\Core\Entity;

/**
 * Designed for use with the Database trait.
 *
 * @property bool $newObject
 * @property \MattyG\Framework\Core\DB $db
 * @property string $_tableName
 * @method string getIdFieldName()
 * @method $this setId(int|string $id)
 * @method mixed getId()
 * @method array|mixed|null getData($key)
 * @method bool hasData($key)
 */
trait DatabaseSave
{
    /**
     * @var bool
     */
    protected $autoIncPK = true;

    /**
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function save()
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        $this->db->startTransaction();
        try {
            $this->_beforeSave();
            $this->_saveData();
            $this->_afterSave();
            $this->db->finishTransaction();
        } catch (\Exception $e) {
            $this->db->cancelTransaction();
            throw $e;
        }
        $this->_afterSaveCommit();
    }

    /**
     * @return $this
     */
    protected function _beforeSave()
    {
        return $this;
    }

    protected function _saveData()
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        $fields = $this->db->fetchTableCols($this->_tableName);
        $data = $this->_prepareDataForSave($fields);
        if ($this->autoIncPK === true) {
            unset($data[$this->getIdFieldName()]);
        }
        if ($this->newObject === true) {
            $this->_saveDataDB($this->_prepareSaveDataInsert($fields), $data);
            $this->setId($this->db->lastInsertId());
            $this->newObject = false;
        } else {
            // Append the ID to the array for the WHERE clause in the UPDATE statement
            $data[] = $this->getId();
            $this->_saveDataDB($this->_prepareSaveDataUpdate($fields), $data);
        }
        return $this;
    }

    /**
     * @param array $fields
     * @return array
     */
    protected function _prepareDataForSave($fields)
    {
        $data = array();
        foreach ($fields as $name => $field) {
            if ($this->hasData($name)) {
                $value = $this->getData($field);
                if ($value === null && $field->notnull) {
                    $data[] = null;
                    //$data[$field] = null;
                } elseif ($value !== null) {
                    $data[] = $value;
                    //$data[$field] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * @param string $query
     * @param array $data
     * @return $this
     * @throws \RuntimeException
     */
    protected function _saveDataDB($query, array $data)
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        $statement = $this->db->newStatement($query);
        $statement->execute($data);
        return $this;
    }

    /**
     * @param array $fields
     * @return string
     */
    protected function _prepareSaveDataInsert(array $fields)
    {
        $query = "INSERT INTO " . $this->_tableName . " (";
        foreach (array_column($fields, "name") as $field) {
            $query .= "`" . $field . "`,";
        }
        $query = rtrim($query, ",") . ") VALUES (";
        $query .= substr(str_repeat(",?", count($fields)), 1) . ")";
        return $query;
    }

    /**
     * @param array $fields
     * @return string
     */
    protected function _prepareSaveDataUpdate(array $fields)
    {
        $query = "UPDATE " . $this->_tableName . " SET ";
        foreach (array_column($fields, "name") as $field) {
            $query .= "`" . $field . "` = ?,";
        }
        $query = rtrim($query, ",") . " WHERE `" . $this->getIdFieldName() . "` = ?";
        return $query;
    }

    /**
     * @return $this
     */
    protected function _afterSave()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function _afterSaveCommit()
    {
        return $this;
    }
}
