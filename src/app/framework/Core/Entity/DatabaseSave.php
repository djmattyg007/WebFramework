<?php

namespace MattyG\Framework\Core\Entity;

trait DatabaseSave
{
    use DataAccess;
    use Database;

    /**
     * @var bool
     */
    protected $autoIncPK = true;

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
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            throw $e;
        }
        $this->_afterSaveCommit();
    }

    protected function _beforeSave()
    {
        return $this;
    }

    protected function _saveData()
    {
        if (!$this->db) {
            throw new \RuntimeException("No database object available.");
        }
        $fields = $this->db->describeTable($this->tableName);
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
        foreach (array_column($fields, "name") as $key => $field) {
            if ($this->hasData($field)) {
                $value = $this->getData($field);
                if ($value === null && $fields[$key]["null"]) {
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
        $query = "INSERT INTO " . $this->tableName . " (";
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
        $query = "UPDATE " . $this->tableName . " SET ";
        foreach (array_column($fields, "name") as $field) {
            $query .= "`" . $field . "` = ?,";
        }
        $query = rtrim($query, ",") . " WHERE `" . $this->getIdFieldName() . "` = ?";
        return $query;
    }

    protected function _afterSave()
    {
        return $this;
    }

    protected function _afterSaveCommit()
    {
        return $this;
    }
}

