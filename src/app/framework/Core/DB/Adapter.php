<?php

namespace MattyG\Framework\Core\DB;

use \MattyG\Framework\Core\DB as DB;

abstract class Adapter
{
    /**
     * @param MattyG\Framework\Core\DB
     */
    protected $db;

    /**
     * @param MattyG\Framework\Core\DB
     */
    public function __construct(DB $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $adapter
     * @param MattyG\Framework\Core\DB $db
     * @return Adapter
     */
    public static function loader($adapter, DB $db)
    {
        $adapter = __NAMESPACE__ . "\\" . ucfirst($adapter);
        return new $adapter($db);
    }
}

