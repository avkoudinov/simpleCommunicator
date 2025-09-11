<?php
//----------------------------------------------------------------------
// class MSSQL_DBWorker
//----------------------------------------------------------------------
class MSSQL_DBWorker extends DBWorker
{
    //--------------------------------------------------------------------
    private $connection = null;
    private $statement = null;
    private $last_query_is_insert = false;
    
    public $row = null;
    public $field_names = null;
    
    public $parameters = null;
    //--------------------------------------------------------------------
    // make another object with the same connection
    // for the cases of exucuting many queries in parrallel
    // to avoid result set conflicts
    //--------------------------------------------------------------------
    function create_clone()
    {
        $cln = new MSSQL_DBWorker();
        
        $cln->is_clone = true;
        
        $cln->db_server = $this->db_server;
        $cln->db_name = $this->db_name;
        $cln->db_user = $this->db_user;
        $cln->db_password = $this->db_password;
        $cln->connection = $this->connection;
        
        return $cln;
    } // create_clone
    
    //--------------------------------------------------------------------
    protected function sys_get_errors()
    {
        $errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
        if (empty($errors)) {
            return "";
        }
        
        $message_array = array();
        
        foreach ($errors as $error) {
            $message_array[$error['message']] = $error['message'];
        }
        
        return implode("\n", $message_array);
    } // sys_get_errors
    
    //--------------------------------------------------------------------
    function __construct($db_server = "", $db_name = "", $db_user = "", $db_password = "")
    {
        $this->db_server = $db_server;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_password = $db_password;
    } // __construct
    
    //--------------------------------------------------------------------
    function __destruct()
    {
        if (!$this->is_clone) {
            $this->close_connection();
        }
    } // __destruct
    
    //--------------------------------------------------------------------
    function is_extension_installed()
    {
        return function_exists("sqlsrv_connect");
    } // is_extension_installed
    
    //--------------------------------------------------------------------
    function get_extension_name()
    {
        return "php_sqlsrv";
    } // get_extension_name
    
    //--------------------------------------------------------------------
    function get_rdbms_name()
    {
        return "Microsoft SQL Server";
    } // get_rdbms_name
    
    //--------------------------------------------------------------------
    function is_connected()
    {
        return (!empty($this->connection) && is_resource($this->connection));
    } // is_connected
    
    //--------------------------------------------------------------------
    function connect($db_server = "", $db_name = "", $db_user = "", $db_password = "", $read_only = false)
    {
        sqlsrv_configure("WarningsReturnAsErrors", 0);
        
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
        
        if (!isset($this->connection) || !$this->connection) {
            if (empty($this->db_server) ||
                empty($this->db_user) ||
                empty($this->db_password)
            ) {
                $this->last_error = "No configuration info available";
                $this->last_error_id = "conf_err";
                return false;
            }
            
            /*
            This new MSSQL dirver has a BIG problem. It sticks to the encoding
            of the Windows and ignores the database and server collation.
      
            If you use the UTF-8, you have two options:
      
            1. Use NCHAR etc. types
            2. Store the UTF-8 strings as is into the 1-byte fields like varchar etc.
      
            The variant 1 might be undesirable because of performance.
      
            The variant 2 might cause problems in contrast to the old MSSQL drivers.
            E.g. if you have the Russian locale on your Windows, but the Latin collation
            in the database, you have no chance for the variant 2. The driver will convert
            the data, although it should not do that but send this as is!
      
            There is the option SQLSRV_ENC_BINARY that prevents any conversion, but it cannot
            be used globally and it cannot be used for simple queries. It can be used only for
            prepared queries. It would be so easy just to allow setting SQLSRV_ENC_BINARY
            on connection level, but they did not do this!
      
            Thus, you have only the following choices:
      
            1. Store the unicode texts in NCHAR fields and use CharacterSet = UTF-8.
            2. Store the unicode texts in normal fields and use only parametrized queries.
               You have always to specify the type SQLSRV_ENC_BINARY for each paramter
               explicitly.
            3. Store the unicode texts in normal fields and ensure that the Windows locale
               and the SQL Server are identical.
      
            Beacause of universality of the DBWorker independent of the database type,
            I use the choise 3.
            */
            
            $config = array(
                "UID" => $this->db_user,
                "PWD" => $this->db_password,
                "CharacterSet" => "UTF-8",
                "ReturnDatesAsStrings" => true
            );
            
            $this->connection = @sqlsrv_connect($this->db_server, $config);
            
            if ($this->connection && !empty($this->db_name) && !$this->use_database($this->db_name)) {
                $this->connection = null;
                return false;
            }
        }
        
        if (!$this->connection) {
            $this->connection = null;
            $this->last_error = $this->sys_get_errors();
            $this->last_error_id = "conn_err";
            
            trigger_error($this->last_error, E_USER_WARNING);
            return false;
        }
        
        return true;
    } // connect
    
    //--------------------------------------------------------------------
    function use_database($db_name)
    {
        if (!$this->connection) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        $this->db_name = $db_name;
        
        $db_name = $this->escape($db_name);
        
        if (!$this->execute_query("USE " . $db_name)) {
            $this->last_error = $this->sys_get_errors();
            $this->last_error_id = "db_err";
            return false;
        }
        
        return true;
    } // use_database
    
    //--------------------------------------------------------------------
    function get_schema()
    {
        return "dbo";
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

        $this->last_query = $query_string;
        
        if (!$this->connection) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        $options = array();
        
        /*
        If the cursor is SQLSRV_CURSOR_STATIC or other than
        SQLSRV_CURSOR_FORWARD, retreiving of the data
        has sometimes very poor performance. Not the query execution,
        but the data retrieving!
    
        The default is SQLSRV_CURSOR_FORWARD, but it is not possible
        to get fetched_count by this type of cursor. So we sacrifice
        the possibility to get fetched_count for the preformance.
        The preformance is more important.
        
        no more relevant in new version of driver
        */
        
        if (preg_match("/\s*SELECT/i", $query_string)) {
            $options = array("Scrollable" => SQLSRV_CURSOR_FORWARD);
        }
        
        $this->statement = @sqlsrv_query($this->connection, $query_string, array(), $options);
        if (!$this->statement) {
            $this->last_error = $this->sys_get_errors();
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

        return true;
    } // execute_query
    
    //--------------------------------------------------------------------
    function prepare_query($query_string)
    {
        if (!$this->connection) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        $this->last_query = $query_string;
        $this->prepared_query = $query_string;
        
        $params = array();
        $this->parameters = array();
        
        $cnt = preg_match_all("/\\?/", $query_string, $matches);
        
        for ($i = 0; $i < $cnt; $i++) {
            $this->parameters[$i] = null;
            $params[$i] = &$this->parameters[$i];
        }
        
        $query_appendix = "";
        
        // to be able to get the insert id from prepared query
        // we have to add this appendix
        
        $this->last_query_is_insert = false;
        if (preg_match("/\s*INSERT/i", $query_string)) {
            $this->last_query_is_insert = true;
            
            $query_appendix = "; SELECT SCOPE_IDENTITY() AS IID";
        }
        
        $options = array();
        /*
        If the cursor is SQLSRV_CURSOR_STATIC or other than
        SQLSRV_CURSOR_FORWARD, retreiving of the data
        has sometimes very poor performance. Not the query execution,
        but the data retrieving!
    
        The default is SQLSRV_CURSOR_FORWARD, but it is not possible
        to get fetched_count by this type of cursor. So we sacrifice
        the possibility to get fetched_count for the preformance.
        The preformance is more important.
    
        if(preg_match("/\s*SELECT/i", $query_string))
        {
          $options = array("Scrollable" => SQLSRV_CURSOR_STATIC);
        }
        */
        
        $this->statement = @sqlsrv_prepare($this->connection, $query_string . $query_appendix, $params, $options);
        if (!$this->statement) {
            $this->last_error = $this->sys_get_errors();
            $this->last_error_id = "query_err";
            
            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
            return false;
        }
        
        return true;
    } // prepare_query
    
    //--------------------------------------------------------------------
    function execute_prepared_query(/* arg list */)
    {
        if (!$this->connection) {
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
        
        $this->last_query = $this->prepared_query;
        
        $counter = 0;
        foreach ($args as $argval) {
            if ($argval === null) {
                $this->parameters[$counter] = null;
                
                $this->last_query = preg_replace("/\\?/", "null", $this->last_query, 1);
            } elseif (is_int($argval)) {
                $this->parameters[$counter] = $argval;
                
                $this->last_query = preg_replace("/\\?/", $argval, $this->last_query, 1);
            } elseif (is_float($argval)) {
                $this->parameters[$counter] = $argval;
                
                $this->last_query = preg_replace("/\\?/", $argval, $this->last_query, 1);
            } else {
                $this->parameters[$counter] = $argval;
                
                $this->last_query = preg_replace("/\\?/", preg_r_escape("'" . $this->escape($argval) . "'"), $this->last_query, 1);
            }
            
            $counter++;
        }
        
        if (!@sqlsrv_execute($this->statement)) {
            $this->last_error = $this->sys_get_errors();
            $this->last_error_id = "query_err";
            
            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
            return false;
        }
        
        return true;
    } // execute_prepared_query
    
    //--------------------------------------------------------------------
    function execute_procedure(/* arg list */)
    {
        if (!$this->connection) {
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
            
            $this->last_query = trim("EXEC {$proc_name} {$arg_list}");
        }
        
        return $this->execute_query($this->last_query);
    } // execute_procedure
    
    //--------------------------------------------------------------------
    function close_connection()
    {
        $this->last_error = null;
        $this->last_error_id = null;
        $this->last_query = null;
        $this->row = null;
        $this->field_names = null;
        
        if (!$this->connection) {
            return true;
        }
        
        if (is_resource($this->connection)) {
            @sqlsrv_close($this->connection);
        }
        
        $this->connection = null;
        $this->parameters = null;
        $this->statement = null;
        $this->last_error = null;
        $this->last_error_id = null;
        
        return true;
    } // close_connection
    
    //--------------------------------------------------------------------
    function start_transaction()
    {
        if (!$this->connection) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        return @sqlsrv_begin_transaction($this->connection);
    } // start_transaction
    
    //--------------------------------------------------------------------
    function commit_transaction()
    {
        if (!$this->connection) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        return @sqlsrv_commit($this->connection);
    } // commit_transaction
    
    //--------------------------------------------------------------------
    function rollback_transaction()
    {
        if (!$this->connection) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        return @sqlsrv_rollback($this->connection);
    } // rollback_transaction
    
    //--------------------------------------------------------------------
    function free_result()
    {
        if ($this->statement) {
            if (is_resource($this->statement)) {
                @sqlsrv_cancel($this->statement);
            }
        }
        
        $this->row = null;
        $this->field_names = null;
        
        return true;
    } // free_result
    
    //--------------------------------------------------------------------
    function free_prepared_query()
    {
        if ($this->statement) {
            if (is_resource($this->statement)) {
                @sqlsrv_free_stmt($this->statement);
            }
        }
        
        $this->statement = null;
        $this->last_query = null;
        $this->prepared_query = null;
        $this->parameters = null;
        
        $this->last_query_is_insert = false;
        
        return true;
    } // free_prepared_query
    
    //--------------------------------------------------------------------
    function fetch_row()
    {
        if (!$this->statement) {
            $this->last_error_id = "result_err";
            return false;
        }
        
        if (!is_resource($this->statement)) {
            return false;
        }
        
        $this->row = @sqlsrv_fetch_array($this->statement, SQLSRV_FETCH_ASSOC);
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
        if (!$this->statement) {
            $this->last_error_id = "result_err";
            return -1;
        }
        
        if (!is_resource($this->statement)) {
            return 0;
        }
        
        /*
        If the cursor is SQLSRV_CURSOR_STATIC or other than
        SQLSRV_CURSOR_FORWARD, retreiving of the data
        has sometimes very poor performance. Not the query execution,
        but the data retrieving!
    
        The default is SQLSRV_CURSOR_FORWARD, but it is not possible
        to get fetched_count by this type of cursor. So we sacrifice
        the possibility to get fetched_count for the preformance.
        The preformance is more important.
    
        no more relevant in new version of driver
        */
        
        return @sqlsrv_num_rows($this->statement);
    } // fetched_count
    
    //--------------------------------------------------------------------
    function affected_count()
    {
        if (!$this->statement) {
            return 0;
        }
        
        return @sqlsrv_rows_affected($this->statement);
    } // affected_count
    
    //--------------------------------------------------------------------
    function field_count()
    {
        if (!$this->statement) {
            return -1;
        }
        
        return @sqlsrv_num_fields($this->statement);
    } // field_count
    
    //--------------------------------------------------------------------
    function insert_id()
    {
        if (!$this->connection) {
            $this->last_error_id = "conn_err";
            return false;
        }
        
        if ($this->last_query_is_insert) {
            if (!$this->statement) {
                return null;
            }
            
            if (!sqlsrv_next_result($this->statement)) {
                $this->last_error = $this->sys_get_errors();
                $this->last_error_id = "query_err";
                
                trigger_error($this->last_error, E_USER_WARNING);
                return false;
            }
            
            $id = null;
            
            if (@sqlsrv_fetch($this->statement)) {
                $id = sqlsrv_get_field($this->statement, 0);
            }
            
            return $id;
        }
        
        $this->statement = @sqlsrv_query($this->connection, "SELECT SCOPE_IDENTITY() AS IID");
        if (!$this->statement) {
            $this->last_error = $this->sys_get_errors();
            $this->last_error_id = "query_err";
            
            trigger_error($this->last_error, E_USER_WARNING);
            return false;
        }
        
        if (!$this->fetch_row()) {
            $this->last_error_id = "query_err";
            return false;
        }
        
        $id = $this->field_by_name("IID");
        
        $this->free_result();
        
        return $id;
    } // insert_id
    
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
    function field_info_by_num($num)
    {
        if (!$this->statement) {
            $this->last_error_id = "result_err";
            return false;
        }
        
        $info = @sqlsrv_field_metadata($this->statement);
        if (!$info) {
            $this->last_error = $this->sys_get_errors();
            $this->last_error_id = "result_err";
            
            trigger_error($this->last_error, E_USER_WARNING);
            return false;
        }
        
        if (empty($info[$num])) {
            return false;
        }
        
        $sqlsrv_type = array();
        
        $sqlsrv_type[-5] = "bigint";
        $sqlsrv_type[-2] = "binary";
        $sqlsrv_type[-7] = "bit";
        $sqlsrv_type[1] = "char";
        $sqlsrv_type[91] = "date";
        $sqlsrv_type[93] = "datetime";
        
        $sqlsrv_type[-155] = "datetimeoffset";
        $sqlsrv_type[3] = "decimal";
        $sqlsrv_type[6] = "float";
        $sqlsrv_type[-4] = "image";
        $sqlsrv_type[4] = "int";
        $sqlsrv_type[-8] = "nchar";
        
        $sqlsrv_type[-10] = "ntext";
        $sqlsrv_type[2] = "numeric";
        $sqlsrv_type[-9] = "nvarchar";
        $sqlsrv_type[7] = "real";
        $sqlsrv_type[5] = "smallint";
        $sqlsrv_type[-1] = "text";
        
        $sqlsrv_type[-154] = "time";
        $sqlsrv_type[-6] = "tinyint";
        $sqlsrv_type[-151] = "udt";
        $sqlsrv_type[-11] = "uniqueidentifier";
        $sqlsrv_type[-3] = "varbinary";
        $sqlsrv_type[12] = "varchar";
        $sqlsrv_type[-152] = "xml";
        
        $res = new stdClass();
        
        $res->name = $info[$num]["Name"];
        $res->type = $info[$num]["Type"];
        $res->length = $info[$num]["Size"];
        $res->binary = 0;
        $res->numeric = 0;
        
        if (!empty($res->type)) {
            if (in_array($res->type, array(-5, 3, 6, 4, 2, 7, 5, -6))) {
                $res->numeric = 1;
            }
            
            if (isset($sqlsrv_type[$res->type])) {
                $res->type = $sqlsrv_type[$res->type];
            }
        }
        
        return $res;
    } // field_info_by_num
    
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
    function escape($str)
    {
        return str_replace("'", "''", "$str");
    } // escape
    
    //--------------------------------------------------------------------
    function quotes_or_null($str)
    {
        return (string)$str === "" ? "NULL" : "N'" . $this->escape($str) . "'";
    } // quotes_or_null
    
    //--------------------------------------------------------------------
    function format_date($date)
    {
        return date("Ymd", $date);
    } // format_date
    
    //--------------------------------------------------------------------
    function format_datetime($datetime)
    {
        return date("Ymd H:i:s", $datetime);
    } // format_datetime
    //--------------------------------------------------------------------
} // class MSSQL_DBWorker
//----------------------------------------------------------------------
?>