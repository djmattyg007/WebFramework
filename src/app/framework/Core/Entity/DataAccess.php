<?php

namespace MattyG\Framework\Core\Entity;

trait DataAccess
{
    /**
     * @var array
     */
    protected $_data = array();

    /**
     * @var string
     */
    protected $_idFieldName = null;

    /**
     * @param int|string $id
     * @return $this
     */
    public function setId($id)
    {
        if ($this->_idFieldName === null) {
            return $this->setData("id", $id);
        } else {
            return $this->setData($this->_idFieldName, $id);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        if ($this->_idFieldName === null) {
            return $this->getData("id");
        } else {
            return $this->getData($this->_idFieldName);
        }
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        if ($this->_idFieldName === null) {
            return "id";
        } else {
            return $this->_idFieldName;
        }
    }

    /**
     * @param array|string|int $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key = array(), $value = null)
    {
        if (is_array($key)) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setDataUsingMethod($key, $value = null)
    {
        $method = "set" . ucfirst($key);
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->setData($key, $value);
        }
        return $this;
    }

    /**
     * @param string|null $key
     * @return array|mixed|null
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->_data;
        } else {
            if (isset($this->_data[$key])) {
                return $this->_data[$key];
            } else {
                return null;
            }
        }
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getDataUsingMethod($key)
    {
        $method = "get" . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return $this->getData($key);
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasData($key)
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        } elseif (array_key_exists($key, $this->_data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function unsetData($key)
    {
        unset($this->_data[$key]);
        return $this;
    }
}

