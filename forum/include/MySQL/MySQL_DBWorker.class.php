<?php
//----------------------------------------------------------------------
// class MySQL_DBWorker
//----------------------------------------------------------------------
class MySQL_DBWorker extends DBWorker
{
    //--------------------------------------------------------------------
    public $mysqli = null;
    public $mysqli_result = null;
    public $statement = null;
    public $prepared_query = null;
    public $row = null;
    public $field_names = null;
    //--------------------------------------------------------------------
    // make another object with the same connection (mysqli)
    // for the cases of exucuting many queries in parrallel
    // to avoid result set conflicts
    //--------------------------------------------------------------------
    function create_clone()
    {
        $cln = new MySQL_DBWorker();
        
        $cln->is_clone = true;
        
        $cln->db_server = $this->db_server;
        $cln->db_name = $this->db_name;
        $cln->db_user = $this->db_user;
        $cln->db_password = $this->db_password;
        $cln->mysqli = $this->mysqli;
        
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
    function is_extension_installed()
    {
        if (!class_exists("MySQLi")) {
            return false;
        }
        
        return true;
    } // is_extension_installed
    
    //--------------------------------------------------------------------
    function get_extension_name()
    {
        return "php_mysqli";
    } // get_extension_name
    
    //--------------------------------------------------------------------
    function get_rdbms_name()
    {
        return "MySQL Server";
    } // get_rdbms_name
    
    //--------------------------------------------------------------------
    function is_connected()
    {
        return (!empty($this->mysqli) && empty($this->mysqli->connect_error));
    } // is_connected
    
    //--------------------------------------------------------------------
    function connect($db_server = "", $db_name = "", $db_user = "", $db_password = "", $read_only = false)
    {
        mysqli_report(MYSQLI_REPORT_OFF);

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
        
        if (!$this->mysqli) {
            if (empty($this->db_server) ||
                empty($this->db_user) ||
                empty($this->db_password)
            ) {
                $this->last_error = "No configuration info available";
                $this->last_error_id = "conf_err";
                return false;
            }
            
            $this->mysqli = new MySQLi($this->db_server, $this->db_user, $this->db_password);
        }
        
        if ($this->mysqli->connect_error) {
            $this->last_error = $this->mysqli->connect_error;
            $this->last_error_id = "conn_err";
            $this->mysqli = null;
            return false;
        }
        
        if (!empty($this->db_name) && !$this->use_database($this->db_name)) {
            $this->mysqli = null;
            return false;
        }
        
        @$this->mysqli->query("set charset utf8mb4");
        
        @$this->mysqli->query("SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED");
        
        if (!empty($read_only)) {
            @$this->mysqli->query("set transaction read only");
            @$this->mysqli->query("start transaction");
        }
        
        return true;
    } // connect
    
    //--------------------------------------------------------------------
    function use_database($db_name)
    {
        if (!$this->mysqli) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        $this->db_name = $db_name;
        
        if (!@$this->mysqli->select_db($this->db_name)) {
            $this->last_error = $this->mysqli->error;
            $this->last_error_id = "db_err";
            trigger_error($this->last_error, E_USER_WARNING);
            return false;
        }
        
        return true;
    } // use_database
    
    //--------------------------------------------------------------------
    function get_schema()
    {
        return "";
    } // get_schema
    
    //--------------------------------------------------------------------
    function qualify_name_with_schema($name)
    {
        return $name;
    } // qualify_name_with_schema
    
    //--------------------------------------------------------------------
    function execute_query($query_string)
    {
        $tmp = microtime(true);
        
        $this->last_query = $query_string;
        
        if (!$this->mysqli) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        $this->mysqli_result = @$this->mysqli->query($query_string);
        if (!$this->mysqli_result) {
            $this->last_error = $this->mysqli->error;
            $this->last_error_id = "query_err";
            
            if (strstr($this->last_error, "Lock wait timeout exceeded") ||
                strstr($this->last_error, "Deadlock")) {
                $lock_dump = "QUERY:\n\n" . $query_string;
    
                $lock_dump .= "\n\nERROR:\n\n" . $this->last_error;

                $res = @$this->mysqli->query("SHOW ENGINE INNODB STATUS");
                if (!empty($res)) {
                    $response = $res->fetch_assoc();
                    if (!empty($response)) {
                        $lock_dump .= "\n\nSHOW ENGINE INNODB STATUS - Succeeded";
                        $lock_dump .= "\n\n" . trim($response["Status"]);
                    } else {
                        $lock_dump .= "\n\nSHOW ENGINE INNODB STATUS - Failed 2";
                    }
                    
                    $res->free_result();
                } else {
                    $lock_dump .= "\n\nSHOW ENGINE INNODB STATUS - Failed 1";
                }
                
                $res = @$this->mysqli->query("SHOW FULL PROCESSLIST");
                if (!empty($res)) {
                    $lock_dump .= "\n\nSHOW FULL PROCESSLIST - Succeeded";
                    
                    while ($response = $res->fetch_assoc()) {
                        $lock_dump .= "\n\n" . trim(print_r($response, true));
                    }
                    
                    $res->free_result();
                } else {
                    $lock_dump .= "\n\n SHOW FULL PROCESSLIST - Failed";
                }
                
                trace_case(trim($lock_dump), "locks");
            }
    
            trigger_error($this->last_error . "\n\n" . $query_string, E_USER_WARNING);
            
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
    //--------------------------------------------------------------------
    // prepared qeries are not supported in MySQL, is just an imitation   //
    // for better porting from/to other databases                         //
    //--------------------------------------------------------------------
    function prepare_query($query_string)
    {
        if (!$this->mysqli) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        $this->last_query = $query_string;
        $this->prepared_query = $query_string;
        
        $this->statement = $this->mysqli->prepare($query_string);
        if (!$this->statement) {
            $this->last_error = $this->mysqli->error;
            $this->last_error_id = "query_err";
            trigger_error($this->last_error . "\n\n" . $query_string, E_USER_WARNING);
            
            return false;
        }
        
        return true;
    } // prepare_query
    
    //--------------------------------------------------------------------
    function execute_prepared_query(/* arg list */)
    {
        if (!$this->mysqli) {
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
        
        $parameters = array();
        $parameters[0] = "";
        
        $this->last_query = $this->prepared_query;
        
        $counter = 1;
        foreach ($args as $argval) {
            if ($argval === null) {
                $parameters[0] .= "i";
                $parameters[$counter] = null;
                
                $this->last_query = preg_replace("/\\?/", "null", $this->last_query, 1);
            } elseif (is_int($argval)) {
                $parameters[0] .= "i";
                $parameters[$counter] = $argval;
                
                $this->last_query = preg_replace("/\\?/", $argval, $this->last_query, 1);
            } elseif (is_float($argval)) {
                $parameters[0] .= "d";
                $parameters[$counter] = $argval;
                
                $this->last_query = preg_replace("/\\?/", $argval, $this->last_query, 1);
            } else {
                $parameters[0] .= "s";
                $parameters[$counter] = $argval;
                
                $this->last_query = preg_replace("/\\?/", preg_r_escape("'" . $this->escape($argval) . "'"), $this->last_query, 1);
            }
            
            $counter++;
        }
        
        if (!call_user_func_array(array($this->statement, 'bind_param'), $parameters)) {
            $this->last_error = "Number of elements in type definition string doesn't match number of bind variables.";
            $this->last_error_id = "query_err";
            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
            
            return false;
        }
        
        if (!$this->statement->execute()) {
            $this->last_error = $this->statement->error;
            $this->last_error_id = "query_err";
            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
            
            return false;
        }
        
        if (!$this->statement->store_result()) {
            $this->last_error = $this->statement->error;
            $this->last_error_id = "query_err";
            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
            
            return false;
        }
        
        if ($this->statement->num_rows) {
            $this->mysqli_result = $this->statement->result_metadata();
        }
        
        return true;
    } // execute_prepared_query
    
    //--------------------------------------------------------------------
    function execute_procedure(/* arg list */)
    {
        if (!$this->mysqli) {
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
            foreach ($args as $argkey => $argval) {
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
            
            $this->last_query = "CALL {$proc_name}({$arg_list});";
        }
        
        return $this->execute_query($this->last_query);
    } // execute_procedure
    
    //--------------------------------------------------------------------
    function free_prepared_query()
    {
        if ($this->statement) {
            @$this->statement->close();
        }
        
        $this->statement = null;
        $this->last_query = null;
        $this->prepared_query = null;
        
        return true;
    } // free_prepared_query
    
    //--------------------------------------------------------------------
    function close_connection()
    {
        $this->last_error = null;
        $this->last_error_id = null;
        $this->last_query = null;
        $this->prepared_query = null;
        $this->row = null;
        $this->field_names = null;
        
        if ($this->mysqli) {
            @$this->mysqli->close();
        }
        
        if ($this->statement) {
            @$this->statement->close();
        }
        
        $this->mysqli = null;
        $this->mysqli_result = null;
        $this->statement = null;
        $this->last_error = null;
        $this->last_error_id = null;
        
        return true;
    } // close_connection
    
    //--------------------------------------------------------------------
    function start_transaction()
    {
        return $this->execute_query("BEGIN");
    } // start_transaction
    
    //--------------------------------------------------------------------
    function commit_transaction()
    {
        return $this->execute_query("COMMIT");
    } // commit_transaction
    
    //--------------------------------------------------------------------
    function rollback_transaction()
    {
        return $this->execute_query("ROLLBACK");
    } // rollback_transaction
    
    //--------------------------------------------------------------------
    function free_result()
    {
        if ($this->mysqli_result) {
            $this->mysqli_result->free_result();
            
            $this->mysqli_result = null;
        }
        
        $this->last_error = null;
        $this->row = null;
        $this->field_names = null;
        $this->last_query = null;
        
        return true;
    } // free_result
    
    //--------------------------------------------------------------------
    function insert_id()
    {
        if (!$this->mysqli) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        return $this->mysqli->insert_id;
    } // insert_id
    
    //--------------------------------------------------------------------
    function fetch_row()
    {
        if ($this->statement) {
            $params = array();
            $this->row = array();
            
            $fcnt = $this->statement->field_count;
            for ($i = 0; $i < $fcnt; $i++) {
                $finfo = $this->field_info_by_num($i);
                if (empty($finfo)) {
                    return false;
                }
                
                $this->row[$finfo->name] = "";
                $params[] = &$this->row[$finfo->name];
            }
            
            if (count($this->row) == 0) {
                return false;
            }
            
            if (!$this->field_names) {
                $this->field_names = array_keys($this->row);
            }
            
            if (!call_user_func_array(array($this->statement, 'bind_result'), $params)) {
                $this->last_error = $this->statement->error;
                $this->last_error_id = "query_err";
                trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
                
                return false;
            }
            
            return $this->statement->fetch();
        }
        
        if (!$this->mysqli_result) {
            $this->last_error_id = "result_err";
            return false;
        }
        
        $this->row = @$this->mysqli_result->fetch_assoc();
        
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
        if ($this->statement) {
            return $this->statement->num_rows;
        }
        
        if (!$this->mysqli_result) {
            $this->last_error_id = "result_err";
            return -1;
        }
        
        // by update, insert, delete it is not an object
        // by select it is an object
        // crazy!
        
        if (!is_object($this->mysqli_result)) {
            return 0;
        }
        
        return $this->mysqli_result->num_rows;
    } // fetched_count
    
    //--------------------------------------------------------------------
    function affected_count()
    {
        if (!$this->mysqli_result) {
            $this->last_error_id = "result_err";
            return -1;
        }
        
        return $this->mysqli->affected_rows;
    } // affected_count
    
    //--------------------------------------------------------------------
    function field_count()
    {
        if ($this->statement) {
            return $this->statement->field_count;
        }
        
        if (!$this->mysqli_result) {
            return 0;
        }
        
        return $this->mysqli_result->field_count;
    } // field_count
    
    //--------------------------------------------------------------------
    function field_by_name($name)
    {
        if (!$this->row) {
            return "";
        }
        
        return $this->row[$name];
    } // field_by_name
    
    //--------------------------------------------------------------------
    function field_by_num($num)
    {
        if (!$this->row) {
            return "";
        }
        
        if (!isset($this->field_names[$num])) {
            return "";
        }
        
        return $this->row[$this->field_names[$num]];
    } // field_by_num
    
    //--------------------------------------------------------------------
    function field_name($num)
    {
        $info = $this->field_info_by_num($num);
        if (!$info) {
            return "";
        }
        
        return val_or_empty($info->name);
    } // field_name
    
    //--------------------------------------------------------------------
    function field_info_by_num($num)
    {
        if (!$this->mysqli_result) {
            $this->last_error_id = "result_err";
            return null;
        }
        
        $res = @$this->mysqli_result->fetch_field_direct($num);
        if (!$res) {
            $this->last_error = $this->mysqli->error;
            $this->last_error_id = "result_err";
            
            trigger_error($this->last_error, E_USER_WARNING);
            return null;
        }
        
        // some corrections for compatible formats
        
        $mysqli_type = array();
        
        $mysqli_type[0] = "decimal";
        $mysqli_type[1] = "tinyint";
        $mysqli_type[2] = "smallint";
        $mysqli_type[3] = "integer";
        $mysqli_type[4] = "float";
        $mysqli_type[5] = "double";
        
        $mysqli_type[7] = "timestamp";
        $mysqli_type[8] = "bigint";
        $mysqli_type[9] = "mediumint";
        $mysqli_type[10] = "date";
        $mysqli_type[11] = "time";
        $mysqli_type[12] = "datetime";
        $mysqli_type[13] = "year";
        $mysqli_type[14] = "date";
        
        $mysqli_type[16] = "bit";
        
        $mysqli_type[246] = "decimal";
        $mysqli_type[247] = "enum";
        $mysqli_type[248] = "set";
        $mysqli_type[249] = "tinyblob";
        $mysqli_type[250] = "mediumblob";
        $mysqli_type[251] = "longblob";
        $mysqli_type[252] = "blob";
        $mysqli_type[253] = "varchar";
        $mysqli_type[254] = "char";
        $mysqli_type[255] = "geometry";
        
        // not implemented!
        $res->binary = 0;
        
        $res->numeric = 0;
        
        if (!empty($res->type)) {
            if (in_array($res->type, array(0, 1, 2, 3, 4, 5, 7, 8, 9, 13, 16, 246))) {
                $res->numeric = 1;
            }
            
            if (isset($mysqli_type[$res->type])) {
                $res->type = $mysqli_type[$res->type];
            }
        }
        
        return $res;
    } // field_info_by_num
    
    //--------------------------------------------------------------------
    function escape($str)
    {
        return preg_replace("/(['\\\\])/", "\\\\$1", $str);
    } // escape
    
    //--------------------------------------------------------------------
    function format_date($date)
    {
        return date("Y-m-d", $date);
    } // format_date
    
    //--------------------------------------------------------------------
    function format_datetime($datetime)
    {
        return date("Y-m-d H:i:s", $datetime);
    } // format_datetime
    //--------------------------------------------------------------------
} // MySQL_DBWorker
//----------------------------------------------------------------------
?>