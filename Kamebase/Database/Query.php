<?php
/**
 * Created by HAlexTM on 09/03/2018 16:04
 */


namespace Kamebase\Database;


use Kamebase\Database\Schema\Schema;
use Kamebase\Database\Type\Delete;
use Kamebase\Database\Type\Insert;
use Kamebase\Database\Type\QueryType;
use Kamebase\Database\Type\Select;
use Kamebase\Database\Type\Update;
use Kamebase\Entity\Entity;
use Kamebase\Exceptions\BadQueryException;

class Query {
    /**
     * @var string the table name where we are going to execute the query
     */
    private $table;

    /**
     * @var QueryType
     */
    private $type;

    /*
     * Query sections (data, conditions...)
     */
    private $sections = [];

    /**
     * @var QueryResponse
     */
    private $response;

    public function __construct(string $table, $type = null) {
        $this->table = $table;
        $this->type = $type;
    }

    public static function table(string $table, $type = null) {
        return new self($table, $type);
    }

    public static function schema() {
        return new Schema();
    }

    public function select($columns = null) {
        $this->type = new Select($this->table);
        return $this->columns($columns);
    }

    public function update($columns = null) {
        $this->type = new Update($this->table);
        return $this->columns($columns);
    }

    public function insert($columns = null) {
        $this->type = new Insert($this->table);
        return $this->columns($columns);
    }

    public function delete() {
        $this->type = new Delete($this->table);
        return $this;
    }


    public function columns($columns) {
        if (!is_null($columns)) {
            if (!is_array($columns)) $columns = array_map("trim", explode(",", $columns));
            $this->sections["columns"] = $columns;
        }
        return $this;
    }

    public function values(...$values) {
        if (is_array($values[0])) {
            $values = $values[0];
        } else if (count($values) < 2) {
            $values = array_map("trim", explode(",", $values[0]));
        }
        $this->sections["values"] = $values;
        return $this;
    }

    public function where(...$where) {
        if (!is_array($where[0])) { // String passed
            if (count($where) > 1) {
                $where = [[$where[0] => $where[1]]];
            }
        } else if (isset($where[0][0]) && is_array($where[0][0])) { // First element is the real array
            $where = $where[0];
        }
        $this->sections["where"] = $where;
        return $this;
    }

    public function desc($column) {
        $this->sections["order"] = "DESC";
        $this->sections["orderBy"] = $column;
        return $this;
    }

    public function limit($limit = 1) {
        $this->sections["limit"] = $limit;
        return $this;
    }

    /**
     * @return QueryResponse
     * @throws BadQueryException
     * @throws \Kamebase\Exceptions\NoDbException
     */
    public function execute() {
        if (is_null($this->type) || !($this->type instanceof QueryType)) {
            throw new BadQueryException("Query type is not set");
        }
        $sql = $this->type->compile($this->sections);
        $this->response = new QueryResponse(DB::query($sql));
        return $this->response;
    }

    public function get($index = 0) {
        if (!isset($this->response)) {
            $this->execute();
        }
        return $this->response->get($index);
    }
}
