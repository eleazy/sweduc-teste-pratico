<?php

// phpcs:ignoreFile

namespace App\Framework\Database;

use DateTime;
use Exception;

/**
 * @deprecated Use Eloquent's query builder instead
 */
class LegacyQueryBuilder
{
    private array $tables = [];
    private ?string $operation = null;
    private ?string $query = null;
    private $query_fields;
    private ?string $query_values = null;
    private ?string $query_keys = null;
    private $query_set;
    private array $query_where = [];
    private $query_group_by;
    private $query_order_by;
    private $query_having;
    private $query_limit;
    private $ignore_failure;
    private bool $select_distinct = false;

    private function __construct($table)
    {
        $this->tables[] = $table;
    }

    public static function on($table)
    {
        return new self($table);
    }

    public function select($fields = '*')
    {
        $this->operation = 'select';
        $this->query_fields = is_array($fields) ? implode(',', $fields) : $fields;
        return $this;
    }

    public function select_distinct($fields = '*')
    {
        $this->select_distinct = true;
        return $this->select($fields);
    }

    public function insert_or_update($query_fields)
    {
        $this->operation = 'insert_or_update';
        $this->query_fields = $this->escape_fields($query_fields);
        return $this;
    }

    public function update($query_fields)
    {
        $this->operation = 'update';
        $this->query_fields = $this->escape_fields($query_fields);
        return $this;
    }

    public function insert($query_fields, $ignore_failure = false)
    {
        $this->operation = 'insert';
        $this->query_fields = $this->escape_fields($query_fields);
        $this->ignore_failure = $ignore_failure;
        return $this;
    }

    public function insert_ignore($query_fields)
    {
        return $this->insert($query_fields, true);
    }

    public function delete()
    {
        $this->operation = 'delete';
        return $this;
    }

    public function inner_join($table, $link)
    {
        $this->tables[] = "INNER JOIN $table ON $link";
        return $this;
    }

    public function left_join($table, $link)
    {
        $this->tables[] = "LEFT JOIN $table ON $link";
        return $this;
    }

    public function right_join($table, $link)
    {
        $this->tables[] = "RIGHT JOIN $table ON $link";
        return $this;
    }

    public function where($clausule)
    {
        $this->query_where[] = $clausule;
        return $this;
    }

    public function where_or($clausule)
    {
        $previous_clause = array_pop($this->query_where);
        $this->query_where[] = "$previous_clause OR $clausule";
        return $this;
    }

    public function where_between($field, $from, $to)
    {
        if(!$from || !$to) {
            return;
        }

        if(!$field
        || !DateTime::createFromFormat('Y-m-d H:i:s', $from)
        || !DateTime::createFromFormat('Y-m-d H:i:s', $to)) {
            throw new Exception("Erro ao processar formato de data do MySQL nos parametros recebidos", 1);
        }

        $this->query_where[] = "$field BETWEEN '$from' AND '$to'";
        return $this;
    }

    public function where_between_if($condition, $field, $from, $to)
    {
        if($condition) {
            $this->where_between($field, $from, $to);
        }
        return $this;
    }

    public function where_if($condition, $whereClausule)
    {
        return $condition ? $this->where($whereClausule) : $this;
    }

    public function where_in($column, $list)
    {
        $list = implode("', '", $list);
        return $this->where("$column IN ('$list')");
    }

    public function group_by($fields)
    {
        if (strlen($this->query_group_by)) {
            $this->query_group_by .= ',';
        }

        $fields = is_array($fields) ? implode(',', $fields) : $fields;
        $this->query_group_by .= $fields;

        return $this;
    }

    public function order_by($fields, $ascending = true)
    {
        if (strlen($this->query_order_by)) {
            $this->query_order_by .= ',';
        }

        if ($ascending) {
            $fields = is_array($fields) ? implode(',', $fields) : $fields;
        } else {
            $fields = is_array($fields) ? implode(' DESC,', $fields).' DESC' : $fields.' DESC';
        }

        $this->query_order_by .= $fields;

        return $this;
    }

    public function order_by_desc($fields)
    {
        return $this->order_by($fields, false);
    }

    public function having($clausule)
    {
        if (strlen($this->query_having)) {
            $this->query_having .= ', ';
        }

        $clausule = is_array($clausule) ? implode(',', $clausule) : $clausule;
        $this->query_having .= $clausule;

        return $this;
    }

    public function having_if($condition, $clausule)
    {
        return $condition ? $this->having($clausule) : $this;
    }

    public function limit($limit)
    {
        $this->query_limit = $limit;
        return $this;
    }

    public function query()
    {
        $mount_query = "mount_" . $this->operation;
        $this->$mount_query();
        return $this->query;
    }

    public function subquery($wrap_select)
    {
        $query = str_replace(";", "", $this->query());
        return $wrap_select ? "SELECT * FROM ($query) x" : $query;
    }

    public function execute()
    {
        $query = $this->query();
        $result = mysql_query($query);
        $error = mysql_error();
        $error_number = mysql_errno();

        return [
            'query' => $query,
            'result' => $result,
            'status' => !!$result,
            'insert_id' => mysql_insert_id(),
            'error' => $error,
            'error_number' => $error_number,
            'affected_rows' => mysql_affected_rows()
        ];
    }

    /**
     * Executes the query and return the results
     *
     * @return Array Array of resulting query
     */
    public function get($column = null)
    {
        $result = $this->execute();
        $result_set = [];
        while ($row = mysql_fetch_assoc($result['result'])) {
            $result_set[] = $column ? $row[$column] : $row;
        }

        return $result_set;
    }

    private function tables()
    {
        return join(" ", $this->tables);
    }

    protected function mount_select()
    {
        $select = $this->select_distinct ? "SELECT DISTINCT" : "SELECT";
        $this->query = "$select ".$this->query_fields." FROM ";
        $this->query .= $this->tables();
        $this->query .= $this->mount_where();
        $this->query .= $this->mount_group_by();
        $this->query .= $this->mount_having();
        $this->query .= $this->mount_order_by();
        $this->query .= $this->mount_limit();
        $this->query .= ';';
    }

    protected function mount_insert_or_update()
    {
        $this->query = "INSERT INTO `".$this->tables()."` ($this->query_keys)
        VALUES ($this->query_values)
        ON DUPLICATE KEY UPDATE $this->query_set;";
    }

    protected function mount_update()
    {
        $this->query = "UPDATE `".$this->tables()."` SET $this->query_set";
        $this->query .= $this->mount_where();
        $this->query .= $this->mount_limit();
        $this->query .= ';';
    }

    protected function mount_insert()
    {
        $this->query = "INSERT ".($this->ignore_failure ? 'IGNORE ' : '');
        $this->query .= "INTO `".$this->tables()."`";
        $this->query .= " (".$this->query_keys.") ";
        $this->query .= " VALUES ($this->query_values);";
    }

    protected function mount_delete()
    {
        $this->query = "DELETE FROM `".$this->tables()."`";
        $this->query .= $this->mount_where();
        $this->query .= ";";
    }

    protected function mount_where()
    {
        return array_reduce($this->query_where, [$this, 'reduce_where_clausule']);
    }

    protected function mount_group_by()
    {
        return strlen($this->query_group_by) ? ' GROUP BY '.$this->query_group_by : '';
    }

    protected function mount_order_by()
    {
        return strlen($this->query_order_by) ? ' ORDER BY '.$this->query_order_by : '';
    }

    protected function mount_having() {
        return strlen($this->query_having) ? ' HAVING '.$this->query_having : '';
    }

    protected function mount_limit()
    {
        return strlen($this->query_limit) ? ' LIMIT '.$this->query_limit : '';
    }

    protected function escape_fields($query_fields)
    {
        $query_fields = array_filter($query_fields, 'strlen');
        $this->query_fields = array_map([$this, 'surround_with_quotes'],
                            array_map('mysql_real_escape_string', $query_fields));

        $this->query_keys = implode(',', array_keys($this->query_fields));
        $this->query_values = implode(',', $this->query_fields);
        $this->query_set = array_reduce(array_keys($this->query_fields), [$this,'reduce_to_update_set']);
    }

    protected function reduce_to_update_set($carry, $item)
    {
        $carry .= strlen($carry) ? ',' : '';
        return $carry." $item = ".$this->query_fields[$item];
    }

    protected function reduce_where_clausule($carry, $item)
    {
        $carry .= !strlen($carry) ? ' WHERE ' : ' AND ';
        return $carry."$item";
    }

    protected function surround_with_quotes($value)
    {
        // Ignora aspas na keyword null
        return (strtolower($value) == "null") ? $value : "'".$value."'";
    }
}
