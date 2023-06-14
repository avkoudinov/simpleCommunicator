<?php
//----------------------------------------------------------------------
// class DBWorker
//----------------------------------------------------------------------
abstract class DBWorker
{
    //--------------------------------------------------------------------
    protected $db_server, $db_name, $db_user, $db_password;
    
    protected $last_error = null;
    protected $last_error_id = null;
    protected $last_query = null;
    protected $is_clone = false;
    
    //--------------------------------------------------------------------
    abstract function is_extension_installed();
    
    abstract function get_rdbms_name();
    
    abstract function get_extension_name();
    
    abstract function create_clone();
    
    abstract function is_connected();
    
    abstract function connect($db_server = "", $db_name = "", $db_user = "", $db_password = "", $read_only = false);
    
    abstract function use_database($db_name);
    
    abstract function get_schema();
    
    abstract function qualify_name_with_schema($name);
    
    abstract function execute_query($query_string);
    
    abstract function execute_procedure(/* arg list */);
    
    abstract function execute_prepared_query(/* arg list */);
    
    abstract function prepare_query($query_string);
    
    abstract function close_connection();
    
    abstract function start_transaction();
    
    abstract function commit_transaction();
    
    abstract function rollback_transaction();
    
    abstract function free_result();
    
    abstract function free_prepared_query();
    
    abstract function fetch_row();
    
    abstract function fetched_count();
    
    abstract function affected_count();
    
    abstract function field_count();
    
    abstract function insert_id();
    
    abstract function field_by_name($name);
    
    abstract function field_by_num($num);
    
    abstract function field_info_by_num($num);
    
    abstract function field_name($num);
    
    abstract function escape($str);
    
    abstract function format_date($date);
    
    abstract function format_datetime($datetime);
    
    //--------------------------------------------------------------------
    function set_connection_data($db_server, $db_name, $db_user, $db_password)
    {
        $this->db_server = $db_server;
        $this->db_name = $db_name;
        $this->db_user = $db_user;
        $this->db_password = $db_password;
    } // set_connection_data
    
    //--------------------------------------------------------------------
    function get_last_error()
    {
        if ($this->last_error === null) return null;
		
		return trim($this->last_error);
    }
    
    //--------------------------------------------------------------------
    function get_last_error_id()
    {
        if ($this->last_error_id === null) return null;

        return trim($this->last_error_id);
    }
    
    //--------------------------------------------------------------------
    function get_last_query()
    {
        if ($this->last_query === null) return null;

        return trim($this->last_query);
    }
    
    //--------------------------------------------------------------------
    function clear_messages()
    {
        $this->last_error = null;
        $this->last_error_id = null;
        $this->last_query = null;
    }
    //--------------------------------------------------------------------
} // class DBWorker
//----------------------------------------------------------------------
?>