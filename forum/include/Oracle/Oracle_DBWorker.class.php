<?php
//----------------------------------------------------------------------
// class Oracle_DBWorker
//
// Requires the OCI8 PHP extension (php-oci8 / php_oci8.dll).
// Enable in php.ini: extension=oci8
// Depends on Oracle Instant Client or a full Oracle Client installation.
//
// Connection string formats for $db_name / $db_server:
//   Easy Connect: "host[:port][/service_name]"   e.g. "192.168.1.1/ORCL"
//   TNS alias:    name defined in tnsnames.ora    e.g. "PROD"
//   Full DSN:     "(DESCRIPTION=(ADDRESS=...)...)"
//
// When $db_server is set, the class builds an Easy Connect string:
//   "$db_server/$db_name"
// When only $db_name is set, it is used as a TNS alias / full DSN directly.
//----------------------------------------------------------------------
class Oracle_DBWorker extends DBWorker
{
    //--------------------------------------------------------------------
    protected $read_only   = false;
    protected $connection  = null;   // OCI8 connection resource
    protected $result      = null;   // OCI8 statement handle (last execute_query)
    protected $statement   = null;   // OCI8 statement handle (prepared query)
    protected $prepared_query = null; // SQL text of the prepared query
    protected $row         = null;   // associative array of the current fetched row
    protected $field_names = null;   // ordered list of column names
    protected $affected    = 0;      // rows affected by the last DML
    protected $commit_flag = OCI_COMMIT_ON_SUCCESS; // rows affected by the last DML

    //--------------------------------------------------------------------
    // make another object sharing the same connection
    // for the cases of executing many queries in parallel
    // to avoid result set conflicts
    //--------------------------------------------------------------------
    function create_clone()
    {
        $cln = new Oracle_DBWorker();

        $cln->is_clone    = true;
        $cln->db_server   = $this->db_server;
        $cln->db_name     = $this->db_name;
        $cln->db_user     = $this->db_user;
        $cln->db_password = $this->db_password;
        $cln->read_only   = $this->read_only;
        // share the underlying connection handle
        $cln->connection  = $this->connection;

        return $cln;
    } // create_clone

    //--------------------------------------------------------------------
    function __construct($db_server = "", $db_name = "", $db_user = "", $db_password = "")
    {
        $this->db_server   = $db_server;
        $this->db_name     = $this->db_name ?? "";
        $this->db_name     = $db_name;
        $this->db_user     = $db_user;
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
    protected function sys_get_errors()
    {
        if ($this->connection) {
            $err = oci_error($this->connection);
        } elseif ($this->result) {
            $err = oci_error($this->result);
        } else {
            $err = oci_error();
        }

        if (empty($err)) {
            return "";
        }

        return $err["message"] . (isset($err["sqltext"]) ? "\nSQL: " . $err["sqltext"] : "");
    } // sys_get_errors

    //--------------------------------------------------------------------
    function is_extension_installed()
    {
        return function_exists("oci_connect");
    } // is_extension_installed

    //--------------------------------------------------------------------
    function get_extension_name()
    {
        return "oci8";
    } // get_extension_name

    //--------------------------------------------------------------------
    function get_rdbms_name()
    {
        return "Oracle Database";
    } // get_rdbms_name

    //--------------------------------------------------------------------
    function is_connected()
    {
        return !empty($this->connection) && is_resource($this->connection);
    } // is_connected

    //--------------------------------------------------------------------
    function connect($db_server = "", $db_name = "", $db_user = "", $db_password = "", $read_only = false)
    {
        if ($this->is_connected()) {
            return true;
        }

        $this->last_error    = null;
        $this->last_error_id = null;
        $this->last_query    = null;

        if (!empty($db_server))   { $this->db_server   = $db_server; }
        if (!empty($db_name))     { $this->db_name      = $db_name; }
        if (!empty($db_user))     { $this->db_user      = $db_user; }
        if (!empty($db_password)) { $this->db_password  = $db_password; }

        $this->read_only = $read_only;

        if (empty($this->db_user) || empty($this->db_password)) {
            $this->last_error    = "Connection data is incomplete";
            $this->last_error_id = "conf_err";
            return false;
        }

        // Build the Oracle connection string.
        // If a host is specified, use Easy Connect syntax: host[:port]/service
        // Otherwise treat db_name as a TNS alias or a full connection descriptor.
        if (!empty($this->db_server)) {
            $connection_string = $this->db_server;
        }

        if (empty($connection_string)) {
            $this->last_error    = "The database / TNS alias must be specified for connection!";
            $this->last_error_id = "conf_err";
            return false;
        }

        // oci_connect() reuses persistent connections with identical parameters;
        // use oci_new_connect() if truly independent connections are required.
        $this->connection = @oci_connect(
            $this->db_user,
            $this->db_password,
            $connection_string,
            "AL32UTF8"          // always use Unicode character set
        );

        if (!$this->connection) {
            $err = oci_error();
            $this->last_error    = !empty($err) ? $err["message"] : "Unknown OCI connection error";
            $this->last_error_id = "conn_err";
            $this->connection    = null;

            trigger_error($this->last_error, E_USER_WARNING);

            return false;
        }

        $stmt = @oci_parse($this->connection, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
        if (!$stmt) {
            $this->last_error    = $this->sys_get_errors();
            $this->last_error_id = "query_err";
            return false;
        }

        $ok = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if (!$ok) {
            $err = oci_error($stmt);
            $this->last_error    = !empty($err) ? $err["message"] : $this->sys_get_errors();
            $this->last_error_id = "query_err";
            @oci_free_statement($stmt);

            return false;
        }

        $stmt = @oci_parse($this->connection, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD'");
        if (!$stmt) {
            $this->last_error    = $this->sys_get_errors();
            $this->last_error_id = "query_err";
            return false;
        }

        $ok = @oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
        if (!$ok) {
            $err = oci_error($stmt);
            $this->last_error    = !empty($err) ? $err["message"] : $this->sys_get_errors();
            $this->last_error_id = "query_err";
            @oci_free_statement($stmt);

            return false;
        }

        return true;
    } // connect

    //--------------------------------------------------------------------
    function use_database($db_name)
    {
        // Oracle does not have a "USE database" concept.
        // The schema/database is determined by the connection credentials.
        // Switching schemas is done via ALTER SESSION SET CURRENT_SCHEMA.
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        $safe = $this->escape($db_name);
        return $this->execute_query("ALTER SESSION SET CURRENT_SCHEMA = $safe");
    } // use_database

    //--------------------------------------------------------------------
    function get_schema()
    {
        return strtoupper($this->db_user);
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
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        // Oracle does not accept a trailing semicolon in plain SQL statements
        // but PL/SQL blocks (BEGIN...END; and CREATE PROCEDURE/FUNCTION/TRIGGER/PACKAGE)
        // require the semicolon after END
        $trimmed = rtrim($query_string);
        $is_plsql = (bool)preg_match('/\bEND\s*\w*\s*;\s*$/i', $trimmed)
                 || (bool)preg_match('/^\s*BEGIN\s+/i', $trimmed);

        if (!$is_plsql) {
            $query_string = rtrim($trimmed, ";");
        } else {
            $query_string = $trimmed;
        }

        $this->last_query = $query_string;

        // Free any previous result before running a new query
        if ($this->result) {
            @oci_free_statement($this->result);
            $this->result = null;
        }

        $this->row         = null;
        $this->field_names = null;
        $this->affected    = 0;

        $stmt = @oci_parse($this->connection, $query_string);
        if (!$stmt) {
            $this->last_error    = $this->sys_get_errors();
            $this->last_error_id = "query_err";

            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);

            return false;
        }

        $this->last_insert_id = 0;

        // For INSERT statements: inject RETURNING id INTO :returning_id
        // only when the target table actually has an "id" column.
        // We check USER_TAB_COLUMNS (current schema) once per table and
        // cache the result in a static array to avoid repeated lookups.
        $is_insert = (bool)preg_match('/^\s*INSERT\s+INTO\s+(\w+)/i', $query_string, $insert_matches);
        $use_returning = false;

        if ($is_insert && !preg_match('/\bRETURNING\b/i', $query_string)) {
            // RETURNING is only supported for INSERT ... VALUES (...), not INSERT ... SELECT
            $is_insert_values = (bool)preg_match('/\bVALUES\s*\(/i', $query_string);
            if ($is_insert_values) {
                $insert_table = strtoupper($insert_matches[1]);
                $use_returning = $this->table_has_id_column($insert_table);
                if ($use_returning) {
                    $query_string .= " RETURNING id INTO :returning_id";
                }
            }
        }

        // Re-parse after possible RETURNING injection
        @oci_free_statement($stmt);
        $stmt = @oci_parse($this->connection, $query_string);
        if (!$stmt) {
            $this->last_error    = $this->sys_get_errors();
            $this->last_error_id = "query_err";

            trigger_error($this->last_error . "\n\n" . $query_string, E_USER_WARNING);

            return false;
        }

        $returning_id = 0;
        if ($use_returning) {
            oci_bind_by_name($stmt, ":returning_id", $returning_id, 20, SQLT_INT);
        }

        $ok = @oci_execute($stmt, $this->commit_flag);
        if (!$ok) {
            $err = oci_error($stmt);
            $this->last_error    = !empty($err) ? $err["message"] : $this->sys_get_errors();
            $this->last_error_id = "query_err";
            @oci_free_statement($stmt);

            trigger_error($this->last_error . "\n\n" . $query_string, E_USER_WARNING);

            return false;
        }

        $this->affected = @oci_num_rows($stmt);
        $this->result   = $stmt;

        if ($use_returning) {
            $this->last_insert_id = (int)$returning_id;
        }

        $tmp = round(1000 * (microtime(true) - $tmp));

        if (!empty($_SESSION["trace_sql"]) &&
            ($_SESSION["trace_sql"] == 1 || $tmp >= $_SESSION["trace_sql"])
        ) {
            $dtrace = debug_backtrace();

            $txt  = $query_string . "\n\n";
            $txt .= (function_exists("extract_call_stack") ? extract_call_stack($dtrace) : "") . "\n\n";
            $txt .= "Elapsed: " . $tmp . "ms\n";
            $txt .= "----------------------------------------------------------------------\n";

            $_SESSION["trace_sql_log"] .= $txt;
        }

        if (!empty($_SESSION["ajax_trace_sql"]) &&
            ($_SESSION["ajax_trace_sql"] == 1 || $tmp >= $_SESSION["ajax_trace_sql"])
        ) {
            $dtrace = debug_backtrace();

            $txt  = $query_string . "\n\n";
            $txt .= (function_exists("extract_call_stack") ? extract_call_stack($dtrace) : "") . "\n\n";
            $txt .= "Elapsed: " . $tmp . "ms\n";
            $txt .= "----------------------------------------------------------------------\n";

            $_SESSION["ajax_trace_sql_log"] .= $txt;
        }

        if (empty($_SESSION["no_db_trace"]) && $tmp > 2000 && !(date("G") == 3 && date("i") >= 0 && date("i") <= 10)) {
            $dtrace = debug_backtrace();

            $txt  = $query_string . "\n\n";
            $txt .= "Elapsed: $tmp ms\n\n";
            $txt .= "User: " . (isset($_SESSION["user_name"]) ? $_SESSION["user_name"] : "") . "\n";
            $txt .= "Time: " . date("d.m.Y H:i:s") . "\n\n";
            $txt .= (function_exists("extract_call_stack") ? extract_call_stack($dtrace) : "") . "\n";
            $txt .= "----------------------------------------------------------------------";

            if (function_exists("trace_message_to_file")) {
                trace_message_to_file($txt, "long_queries.log");
            }
        }

        return true;
    } // execute_query

    //--------------------------------------------------------------------
    // Prepared queries use OCI8 bind variables (:name or positional ?).
    // Internally, ? placeholders are converted to :p1, :p2, ... to match
    // the OCI8 named-bind syntax.
    // execute_prepared_query accepts either a positional list or an
    // associative array [":name" => value] (or ["name" => value]).
    // Values longer than 4000 chars are automatically handled as CLOB.
    // For INSERT ... VALUES statements, RETURNING id INTO :returning_id is
    // automatically injected (same logic as execute_query).
    //--------------------------------------------------------------------
    protected $prepared_use_returning = false; // whether RETURNING was injected

    function prepare_query($query_string)
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if ($this->statement) {
            @oci_free_statement($this->statement);
            $this->statement = null;
        }

        // Convert ? placeholders to :p1, :p2, ...
        $counter = 1;
        $query_string = preg_replace_callback("/\?/", function () use (&$counter) {
            return ":p" . ($counter++);
        }, $query_string);

        // Strip trailing semicolon for plain SQL, keep for PL/SQL
        $trimmed = rtrim($query_string);
        $is_plsql = (bool)preg_match('/\bEND\s*\w*\s*;\s*$/i', $trimmed)
                 || (bool)preg_match('/^\s*BEGIN\s+/i', $trimmed);
        $query_string = $is_plsql ? $trimmed : rtrim($trimmed, ";");

        // Inject RETURNING id INTO :returning_id for INSERT ... VALUES if table has id column
        $this->prepared_use_returning = false;
        $this->last_insert_id = 0;

        if (preg_match('/^\s*INSERT\s+INTO\s+(\w+)/i', $query_string, $insert_matches)
            && !preg_match('/\bRETURNING\b/i', $query_string)
            && preg_match('/\bVALUES\s*\(/i', $query_string)
        ) {
            $insert_table = strtoupper($insert_matches[1]);
            if ($this->table_has_id_column($insert_table)) {
                $query_string .= " RETURNING id INTO :returning_id";
                $this->prepared_use_returning = true;
            }
        }

        $this->last_query    = $query_string;
        $this->prepared_query = $query_string;

        $stmt = @oci_parse($this->connection, $query_string);
        if (!$stmt) {
            $this->last_error    = $this->sys_get_errors();
            $this->last_error_id = "query_err";

            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);

            return false;
        }

        $this->statement = $stmt;

        return true;
    } // prepare_query

    //--------------------------------------------------------------------
    function execute_prepared_query(/* arg list */)
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (empty($this->prepared_query) || empty($this->statement)) {
            $this->last_error    = "No prepared query defined";
            $this->last_error_id = "query_err";
            return false;
        }

        $args = func_get_args();
        if (count($args) == 1 && is_array($args[0])) {
            $args = $args[0];
        }

        $this->last_query  = $this->prepared_query;
        $this->row         = null;
        $this->field_names = null;
        $this->affected    = 0;
        $this->last_insert_id = 0;

        // Detect if associative array was passed: [":name" => value] or ["name" => value]
        $is_assoc = count($args) > 0 && is_string(array_key_first($args));

        // OCI8 bind_by_name needs variables (not literals), so we keep references in $bound.
        // CLOB values (> 4000 chars) need a LOB descriptor instead of a plain string bind.
        $bound = array();
        $clob_descriptors = array();

        foreach ($args as $key => $val) {
            // Build bind name: associative uses key, positional uses :p1, :p2, ...
            if ($is_assoc) {
                $bind_name = (strpos($key, ":") === 0) ? $key : ":" . $key;
            } else {
                $bind_name = ":p" . ($key + 1);
            }

            // Values longer than 4000 chars or wrapped in ClobValue/BlobValue
            // must be bound as LOB descriptors
            if ($val instanceof ClobValue || ($val instanceof BlobValue) ||
                (is_string($val) && strlen($val) > 4000))
            {
                $is_blob = ($val instanceof BlobValue);
                $str     = ($val instanceof ClobValue) ? $val->value
                         : (($val instanceof BlobValue) ? $val->value : $val);

                $lob = oci_new_descriptor($this->connection, OCI_D_LOB);
                if (!$lob) {
                    $this->last_error    = "Failed to create LOB descriptor for $bind_name";
                    $this->last_error_id = "query_err";
                    trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
                    return false;
                }

                $lob->writeTemporary($str, $is_blob ? OCI_TEMP_BLOB : OCI_TEMP_CLOB);
                $clob_descriptors[] = $lob;
                $lob_type = $is_blob ? OCI_B_BLOB : OCI_B_CLOB;

                if (!@oci_bind_by_name($this->statement, $bind_name, $lob, -1, $lob_type)) {
                    $this->last_error    = $this->sys_get_errors();
                    $this->last_error_id = "query_err";
                    trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
                    return false;
                }
            } else {
                $bound[$key] = $val;
                if (!@oci_bind_by_name($this->statement, $bind_name, $bound[$key], -1)) {
                    $this->last_error    = $this->sys_get_errors();
                    $this->last_error_id = "query_err";
                    trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);
                    return false;
                }
            }
        }

        // Bind RETURNING id output parameter if injected during prepare_query.
        // Must be a property (not local var) so the reference stays valid during oci_execute.
        if ($this->prepared_use_returning) {
            $this->prepared_returning_id = 0;
            oci_bind_by_name($this->statement, ":returning_id", $this->prepared_returning_id, 20, SQLT_INT);
        }

        // Reuse $this->result slot for the executed prepared statement
        if ($this->result && $this->result !== $this->statement) {
            @oci_free_statement($this->result);
        }
        $this->result = $this->statement;

        $ok = @oci_execute($this->statement, $this->commit_flag);

        // Free temporary CLOB descriptors after execute
        foreach ($clob_descriptors as $lob) {
            $lob->free();
        }

        if (!$ok) {
            $err = oci_error($this->statement);
            $this->last_error    = !empty($err) ? $err["message"] : $this->sys_get_errors();
            $this->last_error_id = "query_err";

            trigger_error($this->last_error . "\n\n" . $this->last_query, E_USER_WARNING);

            return false;
        }

        $this->affected = @oci_num_rows($this->statement);

        if ($this->prepared_use_returning) {
            $this->last_insert_id = (int)$this->prepared_returning_id;
        }

        return true;
    } // execute_prepared_query

    //--------------------------------------------------------------------
    // execute_procedure calls a stored procedure via an anonymous PL/SQL block.
    // First argument is the procedure name, the rest are its IN arguments.
    // OUT / IN-OUT parameters are not supported through this interface;
    // use execute_query("BEGIN proc(:p1, :p2); END;") with OCI binds directly
    // if OUT parameters are needed.
    //--------------------------------------------------------------------
    function execute_procedure(/* arg list */)
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        $args = func_get_args();

        if (count($args) == 0) {
            $this->last_error    = "Procedure name is required";
            $this->last_error_id = "query_err";
            return false;
        }

        $proc_name = array_shift($args);
        $arg_list  = "";

        foreach ($args as $argval) {
            if ($argval === null) {
                $arg_list .= "null, ";
            } elseif (is_int($argval) || is_float($argval)) {
                $arg_list .= "$argval, ";
            } else {
                $arg_list .= "'" . $this->escape($argval) . "', ";
            }
        }

        $arg_list = rtrim($arg_list, ", ");

        // Use an anonymous PL/SQL block so Oracle executes the procedure
        $this->last_query = "BEGIN $proc_name($arg_list); END;";

        return $this->execute_query($this->last_query);
    } // execute_procedure

    //--------------------------------------------------------------------
    function free_prepared_query()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if ($this->statement && is_resource($this->statement)) {
            if ($this->result === $this->statement) {
                $this->result = null;
            }
            @oci_free_statement($this->statement);
        }
        $this->statement = null;

        $this->prepared_query = null;
        $this->last_query     = null;

        return true;
    } // free_prepared_query

    //--------------------------------------------------------------------
    function close_connection()
    {
        $this->last_query     = null;
        $this->prepared_query = null;
        $this->row            = null;
        $this->field_names    = null;

        if ($this->statement && is_resource($this->statement)) {
            @oci_free_statement($this->statement);
        }
        $this->statement = null;

        if ($this->result && $this->result !== $this->statement && is_resource($this->result)) {
            @oci_free_statement($this->result);
        }
        $this->result = null;

        if ($this->connection && is_resource($this->connection)) {
            @oci_close($this->connection);
        }
        $this->connection = null;

        return true;
    } // close_connection

    //--------------------------------------------------------------------
    function start_transaction()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        $this->commit_flag = OCI_NO_AUTO_COMMIT;
        
        // Oracle is always in an implicit transaction.
        // We only need to mark that we are managing it explicitly
        // so that execute_query uses OCI_NO_AUTO_COMMIT (already the default here).
        // A no-op is fine; the real control is in commit/rollback.
        return true;
    } // start_transaction

    //--------------------------------------------------------------------
    function commit_transaction()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        $ok = @oci_commit($this->connection);
        if (!$ok) {
            $this->last_error    = $this->sys_get_errors();
            $this->last_error_id = "query_err";

            $this->commit_flag = OCI_COMMIT_ON_SUCCESS;

            return false;
        }

        $this->commit_flag = OCI_COMMIT_ON_SUCCESS;
        
        return true;
    } // commit_transaction

    //--------------------------------------------------------------------
    function rollback_transaction()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        $ok = @oci_rollback($this->connection);
        if (!$ok) {
            $this->last_error    = $this->sys_get_errors();
            $this->last_error_id = "query_err";

            $this->commit_flag = OCI_COMMIT_ON_SUCCESS;

            return false;
        }

        $this->commit_flag = OCI_COMMIT_ON_SUCCESS;

        return true;
    } // rollback_transaction

    //--------------------------------------------------------------------
    function free_result()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if ($this->result && $this->result !== $this->statement && is_resource($this->result)) {
            @oci_free_statement($this->result);
        }
        $this->result = null;

        $this->row         = null;
        $this->field_names = null;
        $this->last_query  = null;

        return true;
    } // free_result

    //--------------------------------------------------------------------
    // Oracle does not have a native auto-increment / RETURNING clause
    // in a simple way, so we read the last value of the sequence that
    // execute_query() detects INSERT statements and automatically appends
    // RETURNING id INTO :returning_id — but only when the target table
    // actually has an "id" column (checked via USER_TAB_COLUMNS and cached).
    //--------------------------------------------------------------------
    protected $last_insert_id = 0;
    protected $prepared_returning_id = 0;
    protected $id_column_cache = array(); // table_name => bool

    //--------------------------------------------------------------------
    // Check once (then cache) whether $table has a column named "ID".
    // Uses USER_TAB_COLUMNS which covers the current schema only.
    // For cross-schema inserts qualify the table name and use ALL_TAB_COLUMNS.
    //--------------------------------------------------------------------
    protected function table_has_id_column($table)
    {
        if (isset($this->id_column_cache[$table])) {
            return $this->id_column_cache[$table];
        }

        $result = false;

        $chk = @oci_parse(
            $this->connection,
            "SELECT 1 FROM user_tab_columns WHERE table_name = :tname AND column_name = 'ID'"
        );

        if ($chk) {
            $tname = $table;
            @oci_bind_by_name($chk, ':tname', $tname, 128);
            if (@oci_execute($chk, OCI_NO_AUTO_COMMIT)) {
                $result = (@oci_fetch($chk) !== false);
            }
            @oci_free_statement($chk);
        }

        $this->id_column_cache[$table] = $result;

        return $result;
    } // table_has_id_column

    //--------------------------------------------------------------------
    function insert_id()
    {
        return $this->last_insert_id;
    } // insert_id

    //--------------------------------------------------------------------
    function fetch_row()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (!$this->result) {
            $this->last_error    = "Result is empty!";
            $this->last_error_id = "result_err";
            return false;
        }

        // OCI_ASSOC returns an associative array; OCI_RETURN_NULLS preserves NULL columns
        $row = @oci_fetch_array($this->result, OCI_ASSOC | OCI_RETURN_NULLS);
        
        if ($row === false) {
            $this->row = null;
            return false;
        }

        // Oracle returns column names in UPPERCASE; normalise to lowercase
        // for compatibility with the rest of the application which uses lowercase names
        $normalised = array();
        foreach ($row as $key => $val) {
            if ($val instanceof OCILob) {
                $normalised[strtolower($key)] = $val->load();
            } else {
                $normalised[strtolower($key)] = $val;
            }
        }

        $this->row = $normalised;

        if (!$this->field_names) {
            $this->field_names = array_keys($this->row);
        }

        return true;
    } // fetch_row

    //--------------------------------------------------------------------
    function fetched_count()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (!$this->result) {
            $this->last_error    = "Result is empty!";
            $this->last_error_id = "result_err";
            return false;
        }

        // oci_num_rows() returns rows affected by DML (INSERT/UPDATE/DELETE).
        // For SELECT statements it returns 0; use a counter loop or COUNT(*) query instead.
        return @oci_num_rows($this->result);
    } // fetched_count

    //--------------------------------------------------------------------
    function affected_count()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        return $this->affected;
    } // affected_count

    //--------------------------------------------------------------------
    function field_count()
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (!$this->result) {
            $this->last_error    = "Result is empty!";
            $this->last_error_id = "result_err";
            return false;
        }

        return @oci_num_fields($this->result);
    } // field_count

    //--------------------------------------------------------------------
    function field_by_name($name)
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (!$this->row) {
            return null;
        }

        // Accept both lowercase and uppercase column name lookups
        $key = strtolower($name);

        if (!array_key_exists($key, $this->row)) {
            return null;
        }

        return $this->row[$key];
    } // field_by_name

    //--------------------------------------------------------------------
    function field_by_num($num)
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (!$this->row) {
            return null;
        }

        if (!isset($this->field_names[$num])) {
            return null;
        }

        return $this->row[$this->field_names[$num]];
    } // field_by_num

    //--------------------------------------------------------------------
    function field_name($num)
    {
        if (!$this->is_connected()) {
            $this->last_error    = "Database server not connected!";
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
            $this->last_error    = "Database server not connected!";
            $this->last_error_id = "conn_err";
            return false;
        }

        if (!$this->result) {
            $this->last_error    = "Result is empty!";
            $this->last_error_id = "result_err";
            return false;
        }

        // OCI8 field numbers are 1-based
        $oci_num = $num + 1;

        $name = @oci_field_name($this->result, $oci_num);
        if ($name === false) {
            return false;
        }

        $type     = strtolower(@oci_field_type($this->result, $oci_num));
        $raw_type = @oci_field_type_raw($this->result, $oci_num); // numeric OCI type code
        $size     = @oci_field_size($this->result, $oci_num);
        $prec     = @oci_field_precision($this->result, $oci_num);
        $scale    = @oci_field_scale($this->result, $oci_num);

        $field_info = [];

        $field_info["name"]     = strtolower($name);
        $field_info["type"]     = $type;
        $field_info["size"]     = $size;
        $field_info["prec"]     = $prec;
        $field_info["scale"]    = $scale;

        $field_info["string"]   = in_array($type, ["varchar2", "nvarchar2", "char", "nchar", "clob", "nclob", "long"]) ? 1 : 0;
        $field_info["binary"]   = in_array($type, ["raw", "long raw", "blob", "bfile"]) ? 1 : 0;
        $field_info["numeric"]  = in_array($type, ["number", "float", "binary_float", "binary_double", "integer", "smallint"]) ? 1 : 0;
        $field_info["datetime"] = in_array($type, ["date", "timestamp", "timestamp with time zone", "timestamp with local time zone"]) ? 1 : 0;

        return $field_info;
    } // field_info_by_num

    //--------------------------------------------------------------------
    function escape($str)
    {
        // Oracle uses '' to escape single quotes inside string literals.
        // There is no dedicated escape function in OCI8; use str_replace.
        return str_replace("'", "''", (string)$str);
    } // escape

    //--------------------------------------------------------------------
    function format_datetime($datetime)
    {
        if ($datetime === null || $datetime === "") {
            return "NULL";
        }

        return "TO_TIMESTAMP('" . date("Y-m-d H:i:s", $datetime) . "', 'YYYY-MM-DD HH24:MI:SS')";
    } // format_datetime

    //--------------------------------------------------------------------
    function format_date($date)
    {
        if ($date === null || $date === "") {
            return "NULL";
        }

        return "TO_DATE('" . date("Y-m-d", $date) . "', 'YYYY-MM-DD')";
    } // format_date
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
} // class Oracle_DBWorker
//----------------------------------------------------------------------
?>
