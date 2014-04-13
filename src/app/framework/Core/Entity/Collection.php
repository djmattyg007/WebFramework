<?php

namespace MattyG\Framework\Core\Entity;

trait Collection
{
    /**
     * @var array
     */
    protected $items = array();

    /**
     * @var int
     */
    protected $size = null;

    /**
     * @var bool
     */
    protected $strict = false;

    /**
     * @param int|string $key
     * @param mixed $value
     * @return $this
     * @throws \RuntimeException
     */
    public function addItem($key, $value)
    {
        if ($this->strict === true && isset($this->items[$key])) {
            throw new \RuntimeException("Item already exists in collection.");
        }
        $this->items[$key] = $value;
        return $this;
    }

    /**
     * @param int|string $key
     * @return $this
     * @throws \RuntimeException
     */
    public function removeItem($key)
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        } elseif ($this->strict === true) {
            throw new \RuntimeException("Item does not exist in collection.");
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->items = array();
        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        if ($this->size === null) {
            return 0;
        } else {
            return $this->size;
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return mixed
     */
    public function getFirstItem()
    {
        if (count($this->items)) {
            reset($this->items);
            return current($this->items);
        }
        return null;
    }

    /**
     * @return array
     */
    public function getAllIds()
    {
        $ids = array();
        foreach ($this->items as $item) {
            $ids[] = $item->getId();
        }
        return $ids;
    }
}

