<?php
//----------------------------------------------------------------------
// class PostgreSQL_DBWorker
//----------------------------------------------------------------------
class PostgreSQL_DBWorker extends DBWorker
{
    //--------------------------------------------------------------------
    protected $read_only = false;
    protected $connection = null;
    protected $result = null;
    protected $statement = null;
    protected $prepared_query = null;
    protected $row = null;
    protected $field_names = null;

    //--------------------------------------------------------------------
    // make another object with the same connection (mysqli)
    // for the cases of exucuting many queries in parrallel
    // to avoid result set conflicts
    //--------------------------------------------------------------------
    function create_clone()
    {
        $cln = new PostgreSQL_DBWorker();

        $cln->is_clone = true;

        $cln->db_server = $this->db_server;
        $cln->db_port = $this->db_port;
        $cln->db_name = $this->db_name;
        $cln->db_user = $this->db_user;
        $cln->db_password = $this->db_password;
        $cln->read_only = $this->read_only;

        return $cln;
    } // create_clone
    
    //--------------------------------------------------------------------
    function __construct($db_server = "", $db_name = "", $db_user = "", $db_password = "")
    {
        $this->db_server = $db_server;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_password = $db_password;
    } // function __construct
    
    //--------------------------------------------------------------------
    function __destruct()
    {
        if (!$this->is_clone) {
            $this->close_connection();
        }
    } // __destruct()
    
    //--------------------------------------------------------------------
    protected function sys_get_errors()
    {
        $errors = error_get_last();
        if (empty($errors)) {
            return "";
        }

        $message_array = [];

        foreach ($errors as $error) {
            $message_array[$error['message']] = $error['message'];
        }

        return implode("\n", $message_array);
    } // sys_get_errors

    //--------------------------------------------------------------------
    function is_extension_installed()
    {
        return function_exists("pg_connect");
    } // is_extension_installed
    
    //--------------------------------------------------------------------
    function get_extension_name()
    {
        return "pgsql";
    } // get_extension_name
    
    //--------------------------------------------------------------------
    function get_rdbms_name()
    {
        return "PostgreSQL Server";
    } // get_rdbms_name
    
    //--------------------------------------------------------------------
    function is_connected()
    {
        return (!empty($this->connection) && pg_connection_status($this->connection) == PGSQL_CONNECTION_OK);
    } // is_connected
    
    //--------------------------------------------------------------------
    function connect($db_server = "", $db_name = "", $db_user = "", $db_password = "", $read_only = false)
    {
        if ($this->is_connected()) {
            return true;
        }

        $this->last_error = null;
        $this->last_error_id = null;
        $this->last_query = null;
        
        if (!empty($db_server)) {
            $this->db_server = $db_server;
        }
        if (!empty($db_name)) {
            $this->db_name = $db_name;
        }
        if (!empty($db_user)) {
            $this->db_user = $db_user;
        }
        if (!empty($db_password)) {
            $this->db_password = $db_password;
        }

        if (!$this->connection) {
            if (empty($this->db_server) || empty($this->db_user) || empty($this->db_password)) {
                $this->last_error = "Connection data is incomplete";
                $this->last_error_id = "conf_err";
                return false;
            }

            if (empty($this->db_name)) {
                $this->last_error = "The database must be specified immediately by the connection!";
                $this->last_error_id = "conn_err";
            }

            $connection_string = "connect_timeout=20 options='--client_encoding=UTF8' ";
            if (!empty($this->db_server)) {
                $connection_string .= " host=" . $this->db_server;
            }
            if (!empty($this->db_port)) {
                $connection_string .= " port=" . $this->db_port;
            }
            if (!empty($this->db_user)) {
                $connection_string .= " user=" . $this->db_user;
            }
            if (!empty($this->db_name)) {
                $connection_string .= " dbname=" . $this->db_name;
            }
            if (!empty($this->db_password)) {
                $connection_string .= " password=" . $this->db_password;
            }

            $this->connection = pg_connect($connection_string);
        }

        if (!$this->connection) {
            $this->last_error = $this->sys_get_errors();
            $this->last_error_id = "conn_err";
            $this->connection = null;
            
            trigger_error($this->last_error, E_USER_WARNING);

            return false;
        }
        
        $this->result = @pg_query($this->connection, "SET datestyle = 'ISO, YMD'");
        
        if (!$this->result) {
            $this->last_error = pg_last_error($this->connection);
            $this->last_error_id = "query_err";

            trigger_error($this->last_error . "\n\nSET datestyle = 'ISO, YMD'", E_USER_WARNING);

            return false;
        }

        return true;
    } // connect
    
    //--------------------------------------------------------------------
    function use_database($db_name)
    {
        // Database must be chosen during connection.
        
        return true;
    } // use_database
    
    //--------------------------------------------------------------------
    function get_schema()
    {
        return "public";
    } // get_schema
    
    //--------------------------------------------------------------------
    function qualify_name_with_schema($name)
    {
        $schema = $this->get_schema();

        if (!empty($schema)) {
            $schema .= ".";
        }

        return $schema . $name;
    } // qualify_name_with_schema
    
    //--------------------------------------------------------------------
    function execute_query($query_string)
    {
        $tmp = microtime(true);

        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        $this->last_query = $query_string;

        $this->result = @pg_query($this->connection, $query_string);
        
        if (!$this->result) {
            $this->last_error = pg_last_error($this->connection);
            $this->last_error_id = "query_err";

            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);

            return false;
        }

        $tmp = round(1000 * (microtime(true) - $tmp));
        
        if (!empty($_SESSION["trace_sql"]) &&
            ($_SESSION["trace_sql"] == 1 || $tmp >= $_SESSION["trace_sql"])
        ) {
            $dtrace = debug_backtrace();
            
            $txt = $query_string;
            $txt .= "\n";
            $txt .= "\n";
            $txt .= extract_call_stack($dtrace) . "\n";
            $txt .= "\n";
            $txt .= "Elapsed: " . $tmp . "ms" . "\n";
            $txt .= "----------------------------------------------------------------------";
            $txt .= "\n";
            
            $_SESSION["trace_sql_log"] .= $txt;
        }
        
        if (!empty($_SESSION["ajax_trace_sql"]) &&
            ($_SESSION["ajax_trace_sql"] == 1 || $tmp >= $_SESSION["ajax_trace_sql"])
        ) {
            $dtrace = debug_backtrace();
            
            $txt = $query_string;
            $txt .= "\n";
            $txt .= "\n";
            $txt .= extract_call_stack($dtrace) . "\n";
            $txt .= "\n";
            $txt .= "Elapsed: " . $tmp . "ms" . "\n";
            $txt .= "----------------------------------------------------------------------";
            $txt .= "\n";
            
            $_SESSION["ajax_trace_sql_log"] .= $txt;
        }

        if (empty($_SESSION["no_db_trace"]) && $tmp > 2000 && !(date("G") == 3 && date("i") >= 0 && date("i") <= 10)) {
            $dtrace = debug_backtrace();
            
            $txt = $query_string . "\n";
            $txt .= "\n";
            $txt .= "Elapsed: $tmp ms" . "\n";
            $txt .= "\n";
            $txt .= "User: " . val_or_empty($_SESSION["user_name"]) . "\n";
            $txt .= "Time: " . date("d.m.Y H:i:s") . "\n";
            $txt .= "\n";
            $txt .= extract_call_stack($dtrace) . "\n";
            $txt .= "----------------------------------------------------------------------";
            
            trace_message_to_file($txt, "long_queries.log");
        }
        
        return true;
    } // execute_query

    protected $param_order = array(); // ordered list of param names from prepare_query

    function prepare_query($query_string)
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if ($this->statement) {
            @pg_free_result($this->statement);
            $this->statement = null;
        }

        $this->param_order = array();

        // Convert named :name params to $1, $2, ... and record order
        $counter = 1;
        $query_string = preg_replace_callback(
            '/:([\w]+)/',
            function ($m) use (&$counter) {
                $this->param_order[] = $m[1];
                return '$' . ($counter++);
            },
            $query_string
        );

        // Also handle positional ? placeholders (no names recorded)
        if (empty($this->param_order)) {
            $query_string = preg_replace_callback("/\\?/", function () use (&$counter) {
                return '$' . ($counter++);
            }, $query_string);
        }

        $this->last_query = $query_string;
        $this->prepared_query = $query_string;

        $this->statement = @pg_prepare($this->connection, "", $query_string);
        if (!$this->statement) {
            $this->last_error = pg_last_error($this->connection);
            $this->last_error_id = "query_err";
            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
            return false;
        }

        return true;
    } // prepare_query

    //--------------------------------------------------------------------
    function execute_prepared_query(/* arg list */)
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (empty($this->prepared_query) || empty($this->statement)) {
            $this->last_error = "no prepared query defined";
            $this->last_error_id = "query_err";
            return false;
        }

        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        // If associative array and param_order recorded — expand values by order,
        // duplicating values for repeated parameter names
        if (!empty($this->param_order) && count($args) > 0 && is_string(array_key_first($args))) {
            $named = array();
            foreach ($args as $k => $v) {
                $named[ltrim($k, ':')] = $v;
            }
            $positional = array();
            foreach ($this->param_order as $name) {
                $positional[] = isset($named[$name]) ? $named[$name] : null;
            }
            $args = $positional;
        } elseif (count($args) > 0 && is_string(array_key_first($args))) {
            $args = array_values($args);
        }

        $this->last_query = $this->prepared_query;

        // Unwrap ClobValue/BlobValue — PostgreSQL treats both as strings
        $args = array_map(function($v) {
            if ($v instanceof ClobValue || $v instanceof BlobValue) return $v->value;
            return $v;
        }, $args);

        $this->result = @pg_execute($this->connection, "", $args);
        if (!$this->result) {
            $this->last_error = pg_last_error($this->connection);
            $this->last_error_id = "query_err";
            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
            return false;
        }

        return true;
    } // execute_prepared_query
    
    //--------------------------------------------------------------------
    function execute_procedure(/* arg list */)
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        $args = func_get_args();

        // prepare the arguments for placing in eval()
        // escape single quotes

        $this->last_query = "";

        if (count($args) > 0) {
            $proc_name = "";
            $arg_list = "";

            $first = true;
            foreach ($args as $argval) {
                if ($first) {
                    $proc_name = $argval;
                    $first = false;
                    continue;
                }

                if ($argval === null) {
                    $arg_list .= "null, ";
                } elseif (is_int($argval)) {
                    $arg_list .= "$argval, ";
                } elseif (is_float($argval)) {
                    $arg_list .= "$argval, ";
                } else {
                    $arg_list .= "'" . $this->escape($argval) . "', ";
                }
            }

            $arg_list = trim($arg_list, ", ");

            $this->last_query = "CALL $proc_name($arg_list);";
        }

        return $this->execute_query($this->last_query);
    } // execute_procedure
    
    //--------------------------------------------------------------------
    function free_prepared_query()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if ($this->statement) {
            @pg_free_result($this->statement);

            $this->statement = null;
        }

        $this->statement = null;
        $this->last_query = null;
        $this->prepared_query = null;

        return true;
    } // free_prepared_query
    
    //--------------------------------------------------------------------
    function close_connection()
    {
        $this->last_query = null;
        $this->prepared_query = null;
        $this->row = null;
        $this->field_names = null;

        if ($this->statement) {
            @pg_free_result($this->statement);
        }

        if ($this->result) {
            @pg_free_result($this->result);
        }

        if ($this->connection) {
            @pg_close($this->connection);
        }

        $this->statement = null;
        $this->connection = null;
        $this->result = null;
        
        return true;
    } // close_connection
    
    //--------------------------------------------------------------------
    function start_transaction()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        return $this->execute_query("BEGIN");
    } // start_transaction
    
    //--------------------------------------------------------------------
    function commit_transaction()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        return $this->execute_query("COMMIT");
    } // commit_transaction
    
    //--------------------------------------------------------------------
    function rollback_transaction()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        return $this->execute_query("ROLLBACK");
    } // rollback_transaction
    
    //--------------------------------------------------------------------
    function free_result()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if ($this->result) {
            @pg_free_result($this->result);

            $this->result = null;
        }

        $this->row = null;
        $this->field_names = null;
        $this->last_query = null;
        
        return true;
    } // free_result
    
    //--------------------------------------------------------------------
    function insert_id()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        $this->execute_query("select lastval() as iid");

        if (!$this->fetch_row()) {
            $this->last_error = "Identity field cannot be retrieved";
            $this->last_error_id = "query_err";
            
            trigger_error($this->last_error . "\n\nselect lastval() as iid", E_USER_WARNING);

            return true;
        }

        $id = $this->field_by_name("iid");

        $this->free_result();

        return $id;
    } // insert_id
    
    //--------------------------------------------------------------------
    function fetch_row()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if (!$this->result) {
            $this->last_error = "Result is empty!";
            $this->last_error_id = "result_err";

            return true;
        }

        $this->row = @pg_fetch_array($this->result, null, PGSQL_ASSOC);

        if (!$this->row) {
            return false;
        }

        if (!$this->field_names) {
            $this->field_names = array_keys($this->row);
        }

        return true;
    } // fetch_row
    
    //--------------------------------------------------------------------
    function fetched_count()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if (!$this->result || !is_object($this->result)) {
            $this->last_error = "Result is empty!";
            $this->last_error_id = "result_err";

            return false;
        }

        return pg_affected_rows($this->result);
    } // fetched_count
    
    //--------------------------------------------------------------------
    function affected_count()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if (!$this->result || !is_object($this->result)) {
            $this->last_error = "Result is empty!";
            $this->last_error_id = "result_err";

            return false;
        }

        return pg_affected_rows($this->result);
    } // affected_count
    
    //--------------------------------------------------------------------
    function field_count()
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if (!$this->result || !is_object($this->result)) {
            $this->last_error = "Result is empty!";
            $this->last_error_id = "result_err";

            return false;
        }

        return pg_num_fields($this->result);
    } // field_count
    
    //--------------------------------------------------------------------
    function field_by_name($name)
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if (!$this->row) {
            return null;
        }

        if (!array_key_exists($name, $this->row)) {
            return null;
        }

        return $this->row[$name];
    } // field_by_name
    
    //--------------------------------------------------------------------
    function field_by_num($num)
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if (!$this->row) {
            return null;
        }

        if (!array_key_exists($num, $this->field_names)) {
            return null;
        }

        return $this->row[$this->field_names[$num]];
    } // field_by_num
    
    //--------------------------------------------------------------------
    function field_name($num)
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        $info = $this->field_info_by_num($num);
        if (!$info) {
            return null;
        }

        return $info["name"] ?? "";
    } // field_name
    
    //--------------------------------------------------------------------
    function field_info_by_num($num)
    {
        if (!$this->is_connected()) {
            $this->last_error = "Database server not connected!";
            $this->last_error_id = "conn_err";
            
            return false;
        }

        if (!$this->result) {
            $this->last_error = "Result is empty!";
            $this->last_error_id = "result_err";

            return false;
        }

        $field_info = [];

        $field_info["name"] = pg_field_name($this->result, $num);
        $field_info["type"] = pg_field_type($this->result, $num);
        $field_info["size"] = pg_field_size($this->result, $num);

        $field_info["string"] = in_array($field_info["type"], ["text", "varchar", "char"]) ? 1 : 0;
        $field_info["binary"] = ($field_info["type"] == "bytea") ? 1 : 0;
        $field_info["numeric"] = ($field_info["type"] == "int" || $field_info["type"] == "float" || $field_info["type"] == "numeric") ? 1 : 0;
        $field_info["datetime"] = ($field_info["type"] == "timestamp" || $field_info["type"] == "date") ? 1 : 0;

        return $field_info;
    } // field_info_by_num
    
    //--------------------------------------------------------------------
    function escape($str)
    {
        return pg_escape_string($this->connection, $str);
    } // escape
    
    //--------------------------------------------------------------------
    function format_date($date)
    {
        if ($date === null || $date === "") {
            return "NULL";
        }

        return "'" . date("Y-m-d", $date) . "'";
    } // format_date
    
    //--------------------------------------------------------------------
    function format_datetime($datetime)
    {
        if ($datetime === null || $datetime === "") {
            return "NULL";
        }

        return "'" . date("Y-m-d H:i:s", $datetime) . "'";
    } // format_datetime
    //--------------------------------------------------------------------
    function format_date_bind($date)
    {
        if ($date === null || $date === "") {
            return null;
        }

        return date("Y-m-d", $date);
    } // format_date_bind
    
    //--------------------------------------------------------------------
    function format_datetime_bind($datetime)
    {
        if ($datetime === null || $datetime === "") {
            return null;
        }

        return date("Y-m-d H:i:s", $datetime);
    } // format_datetime_bind
    //--------------------------------------------------------------------
} // PostgreSQL_DBWorker
//----------------------------------------------------------------------
?>