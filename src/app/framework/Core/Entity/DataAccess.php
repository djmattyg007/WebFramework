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
     * Convenience wrapper for setData(idFieldName, value).
     *
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
     * Convenience wrapper for getData(idFieldName).
     *
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
     * Returns the name of the field used as the primary key for the object.
     *
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
     * Puts a value into the internal data array, overriding an old value if
     * necessary.
     * If a single paramter is supplied and that parameter is an array, it
     * will completely replace the internal data array.
     *
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
     * Convenience wrapper for setData() that will instead call a function to
     * set a value instead, if one exists.
     * For example, if you have a piece of data for a field named "address",
     * it will first check for the existence of a method named "setAddress" in
     * the class. If it does, it will use that instead of setData().
     *
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
     * Gets a value from the internal data array.
     * If no parameter is supplied, the internal data array is returned
     * instead.
     *
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
     * Convenience wrapper for getData() that will instead call a function to
     * retrieve a value instead, if one exists.
     * For example, if you have a piece of data for a field named "address",
     * it will first check for the existence of a method named "getAddress" in
     * the class. If it does, it will use that instead of getData().
     *
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
     * Checks if there is a key in the internal data array that matches the
     * key parameter passed to the function. It will return true if a key
     * exists, even if the value for that key is null or something equating to
     * null.
     * If you pass an empty parameter (null, empty string, etc) to the
     * function, it will instead tell if you if the internal data array has any
     * data in it at all.
     *
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
     * Delete an element from the internal data array.
     *
     * @param mixed $key
     * @return $this
     */
    public function unsetData($key)
    {
        unset($this->_data[$key]);
        return $this;
    }
}

