<?php

class ForumAPIException extends \Exception
{
    const ERR_CODE_SYSTEM_ERROR = "system_error";
    const ERR_CODE_MAINTENANCE_ERROR = "maintenance_error";
    const ERR_CODE_CONFIG_ERROR = "config_error";
    const ERR_CODE_PROCESSING_ERROR = "processing_error";
    const ERR_CODE_MISSING_REQUEST_DATA = "missing_request_data";
    const ERR_CODE_INVALID_REQUEST_DATA = "invalid_request_data";
    const ERR_CODE_INVALID_CONTENT_TYPE = "invalid_content_type";
    const ERR_CODE_NOT_FOUND_ERROR = "not_found";
    const ERR_CODE_LOGIN_ERROR = "login_error";
    const ERR_CODE_ACCESS_ERROR = "no_access";
    const ERR_CODE_DATABASE_ERROR = "database_error";
    const ERR_CODE_JSON_PARSE_ERROR = "json_parse_error";
    
    protected $error_code = null;
    
    public function __construct($message, $error_code)
    {
        parent::__construct($message);
        
        $this->error_code = $error_code;
    }
    
    public function getErrorCode()
    {
        return $this->error_code;
    }
} // ForumAPIException

?>