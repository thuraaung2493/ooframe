<?php

/**
* TH'database Library
* Select All => DB::table('table_name')->columnsAll()->get();
* Select by Column => DB::table('table_name')->selectColumns(col1,col2,...)->get();
* Filter => DB::table('table_name')->selectColumns(col1,...)->where('name', 'filters', 'value')->
*            get(); <=> filters =>" = , != , < , > , <=, >=, like, ... "
* Insert Data => DB::table('table_name')->insert($data) <=> $data is array type;
* Update Data => DB::table('table_name')->where('name', 'filters', 'value')->update($u_data);
* Delete => DB::table('table_name')->where('name', 'filters', 'value')->delete();
* Truncate or Table Reset => DB::table('table_name')->truncate();
* Order By => ->DB::table('table_name')->selectColumns(col1,col2,...)->orderBy('col', 'sort')->
*            get();
* Group By => ->DB::table('table_name')->selectColumns(col1,col2,...)->groupBy('col')->
*            get();
*/

class DB extends PDO {

    private static $_instance;

    private $table_name;
    private $sql;
    private $where_status;
    private $where;

    private $engine;
    private $host;
    private $database;
    private $user;
    private $pass;

    public function __construct() {
        echo "DB Construct! <br>";
        $this->engine = Config::get('database.engine');
        $this->host = Config::get('database.host');
        $this->database = Config::get('database.database');
        $this->user = Config::get('database.user');
        $this->pass = Config::get('database.pass');
        // mysql:dbname=wpa28db;host=localhost
        $dns = $this->engine . ':dbname=' . $this->database . ";host=" . $this->host;
        // var_dump($dns);
        try {
            $conn = parent::__construct( $dns, $this->user, $this->pass );
        } catch (PDOException $e) {
            echo "Something wrong. Database connection failed.";
        }

    }

    public static function table($table_name) {
        if(!self::$_instance instanceof DB) {
            self::$_instance = new DB();
        }
        self::$_instance->table_name = $table_name;
        self::$_instance->sql = "";
        self::$_instance->where_status = false;
        self::$_instance->where = "";
        return self::$_instance;
    }

    /**
    * Prepare state
    * Select All
    */
    public function columnsAll() {
        $this->sql = "SELECT * FROM " . $this->table_name;

        return $this;
    }

    /**
    * Prepare state
    * Select columns
    * selectColumns($col1, $col2, ...)
    */
    public function selectColumns(string ...$col) {
        $columns = implode(", ", $col);
        $this->sql = "SELECT " . $columns . " FROM " . $this->table_name;

        return $this;
    }

    /**
    * Filter state
    * Where
    * where('col_name', '= < > !=', 'val')
    */
    public function where() {
        // $this->where_status = true;
        $arg = func_get_args();
        if (gettype(end($arg)) == "string") {
            $val = "'" . end($arg) . "'";
        } else {
            $val = end($arg);
        }
        array_pop($arg);
        $where_clause = implode(" ", $arg);
        $this->where = " WHERE " . $where_clause . " " . $val;
        $this->sql .= $this->where;
        return $this;
    }

    /**
    * Order By
    * orderBy($column, $sort) <=> $soft = ASC(default) || DESC
    */
    public function orderBy($column, $sort="ASC"){
        if($sort == "ASC"){
            $this->sql .= " ORDER BY " .  $column;
        }else{
            $args = func_get_args();
            $order_by = implode(" ",$args);
            $this->sql .= " ORDER BY " .  $order_by;
        }
        return $this;
    }

    /**
    * Group By
    * groupBy(string $column)
    */
    public function groupBy(string $column) {
        $this->sql .= " GROUP BY " . $column;
        return $this;
    }

    /**
    * Real query state
    * Get data || Fetch Data
    */
    public function get() {
        var_dump($this->sql);
        $prep = $this->prepare($this->sql);
        $prep->execute();
        $result = $prep->fetchAll(PDO::FETCH_ASSOC);
        if($result == false) {
            trigger_error("Table not found", E_USER_ERROR);
        }
        return $result;
    }

    /**
    * Insert
    * insert($data) <=> $data = []
    */
    // INSERT INTO students (col_names) values ('val1', 'val2')
    public function insert(array $data) {
        $keys = array_keys($data);
        $i_keys = implode(", ", $keys);
        $col_names = " (" . $i_keys . ") ";
        $this->sql = "INSERT INTO " . $this->table_name . $col_names;
        $values = '';
        foreach ($data as $value) {
            if (gettype($value) == "string") {
                $values .= "'" . $value . "', ";
            }else {
                $values .= $value . ", ";
            }
        }
        $r_values = rtrim($values, ", ");
        $data = " (" . $r_values . ")";
        $this->sql .= " values " . $data;
        var_dump($this->sql);
        $prep = $this->prepare($this->sql);
        $result = $prep->execute();
        if ($result) {
            echo "<b>Insert Successfully</b>";
        } else {
            echo "<b>Insert Failed</b>";
        }
    }

    /**
    * Update
    * update(array $u_data)
    * if not include where, all columns will update
    */
    public function update(array $u_data) {
        $pre_data = "";
        foreach ($u_data as $key => $value) {
            $pre_data .= $key . " = ";
            if(gettype($value) == "string") {
                $pre_data .= "'" . $value . "', ";
            }
            else {
                $pre_data .= $value . ", ";
            }
        }
        $update_data = rtrim($pre_data, ", ");
        $this->sql = "UPDATE " . $this->table_name . " SET " . $update_data . $this->where;
        var_dump($this->sql);
        $prep = $this->prepare($this->sql);
        $result = $prep->execute();
        if($result) {
            echo "<b>Updated Successfully</b> <br>";
        } else {
            echo "<b>Updated Failed</b> <br>";
        }
    }

    /**
    * Delete
    * delete()
    * don't miss where. If you miss where, delete all.
    */
    public function delete() {
        if ($this->where) {
            $this->sql = "DELETE FROM " . $this->table_name . " " . $this->where;
        } else {
            echo "<b>Warning!, <em>missing where clause</em></b>";
            die();
        }
        $prep = $this->prepare($this->sql);
        $result = $prep->execute();
        if ($result) {
            echo "<b>Delete Successfully!</b>";
        } else {
            echo "<b>Delete Failed!</b>";
        }
    }

    /**
    * Truncate || Clear all data || Reset table
    * truncate()
    */
    public function truncate() {
        $this->sql = "TRUNCATE TABLE " . $this->table_name;
        $prep = $this->prepare($this->sql);
        $result = $prep->execute();
        if($result == true) {
            echo "Table all reset";
        }
    }

    /**
    * Destructor
    */
    public function __destruct() {
        echo "<br> DB Destructed! <br>";
    }
}

?>
