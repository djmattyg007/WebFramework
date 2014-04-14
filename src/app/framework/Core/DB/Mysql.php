<?php

namespace MattyG\Framework\Core\DB;

class Mysql extends Adapter
{
    /**
     * @param string $table
     * @param string $column
     * @return array
     */
    public function describeTable($table, $column = null)
    {
        $describeQuery = "DESCRIBE $table";
        $cacheId = "$table";
        if (is_string($column)) {
            $describeQuery .= " $column";
            $cacheId .= "_$column";
        }
        if ($cachedResult = $this->db->getQueryCache("describe_processed_$cacheId")) {
            return $cachedResult;
        }
        if ($cachedResult = $this->db->getQueryCache($describeQuery)) {
            $columns = $cachedResult;
        } else {
            $statement = $this->newStatement($describeQuery);
            $statement->execute();
            $columns = $statement->fetchAll(PDO::FETCH_ASSOC);
            $this->db->saveQueryCache($describeQuery, $columns);
        }

        $returnColumns = array();
        foreach ($columns as $column) {
            $array = array();
            $array["name"] = $column["Field"];
            $array["type"] = $column["Type"];
            if ($pos = strpos($array["type"], "(")) {
                $array["type"] = strstr($array["type"], "(", true);
            }
            if ($column["Null"] == "NO") {
                $array["null"] = false;
            } else {
                $array["null"] = true;
            }
            if ($column["Key"] == "PRI") {
                $array["primary"] = true;
            } else {
                $array["primary"] = false;
            }
            $array["default"] = $column["Default"];
            $returnColumns[] = $array;
        }
        $this->db->saveQueryCache("describe_processed_$cacheId", $returnColumns);
        return $returnColumns;
    }
}

