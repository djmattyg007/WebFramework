<?php

namespace MattyG\Framework\Core\Entity;

use \MattyG\Framework\Core\DB as DB;

trait Database
{
    /**
     * @var string
     */
    protected $_tableName = null;

    /**
     * @var bool
     */
    protected $_useQueryBuilder = false;

    /**
     * @var \MattyG\Framework\Core\DB
     */
    protected $db;

    /**
     * @var bool
     */
    protected $newObject = true;

    /**
     * @param MattyG\Framework\Core\DB $db
     * @return $this
     */
    public function setDB(DB $db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * @param bool $isNew
     * @return $this
     */
    public function setNewObject($isNew)
    {
        $this->newObject = $isNew;
        return $this;
    }

    /**
     * @return bool
     */
    public function getNewObject()
    {
        return $this->newObject;
    }
}

