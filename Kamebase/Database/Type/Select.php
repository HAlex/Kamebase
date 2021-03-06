<?php
/**
 * Created by HAlexTM on 09/03/2018 17:35
 */


namespace Kamebase\Database\Type;


class Select extends QueryType {

    public function compile(array $sections) {
        $columns = isset($sections["columns"]) ? $this->getColumns($sections["columns"]) : "*";
        $sql = "SELECT " . $columns . " FROM " . $this->getTable();

        if (isset($sections["where"])) {
            $sql .= " " . $this->compileWhere($sections["where"]);
        }

        if (isset($sections["orderBy"]) && isset($sections["order"])) {
            $sql .= " ORDER BY " . $this->getColumn($sections["orderBy"]) . " " . $sections["order"];
        }

        return $sql . $this->getLimit($sections);
    }
}