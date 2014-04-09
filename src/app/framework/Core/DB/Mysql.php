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
        if (is_string($column)) {
            $describeQuery .= " $column";
        }
        $statement = $this->newStatement($describeQuery);
        $statement->execute();
        $columns = $statement->fetchAll(PDO::FETCH_ASSOC);
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
        return $arrayColumns;
    }
}

