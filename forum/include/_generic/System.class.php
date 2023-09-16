<?php
//-------------------------------------------------------------------
// class System
//-------------------------------------------------------------------
class System
{
    //-----------------------------------------------------------------
    static $db_type;
    static $db_prefix;
    static $interfaces;
    
    //-----------------------------------------------------------------
    static function setDBType($db_type)
    {
        self::$db_type = $db_type;
    } // setDBType
    
    //-----------------------------------------------------------------
    static function getDBType()
    {
        if (empty(self::$db_type)) {
            if (defined('DB_TYPE')) {
                self::$db_type = DB_TYPE;
            } else {
                self::$db_type = "MySQL";
            }
        }
        
        return self::$db_type;
    } // getDBType
    
    //-----------------------------------------------------------------
    static function setDBPrefix($db_prefix)
    {
        self::$db_prefix = $db_prefix;
    } // setDBPrefix
    
    //-----------------------------------------------------------------
    static function getDBPrefix()
    {
        if (empty(self::$db_prefix)) {
            if (defined('DB_PREFIX')) {
                self::$db_prefix = DB_PREFIX;
            } else {
                self::$db_prefix = "V1";
            }
        }
        
        return self::$db_prefix;
    } // getDBPrefix
    
    //-----------------------------------------------------------------
    static function getDBUserPrefix()
    {
        return self::getDBPrefix();
    } // getDBUserPrefix
    
    //-----------------------------------------------------------------
    static function getClassInstance($class_name)
    {
        global $SUPPORTED_DATABASES;
        
        $dbtype = self::getDBType();
        
        if (class_exists($dbtype . "_" . $class_name)) {
            $class_name = $dbtype . "_" . $class_name;
        }
        
        try {
            $class = new ReflectionClass($class_name);
            
            return $class->newInstance();
        } catch (Exception $e) {
            if (!empty($SUPPORTED_DATABASES[$dbtype])) {
                $dbtype_name = $SUPPORTED_DATABASES[$dbtype];
            } else {
                $dbtype_name = $dbtype;
            }
            
            MessageHandler::setError(text("ErrFilesMissingOrHaveErrors"), sprintf(text("ErrCreatingClassInstance"), $class_name, $dbtype_name));
        }
        
        return null;
    } // getClassInstance
    //-----------------------------------------------------------------
    /**
     * @returns DBWorker
     */
    static function getRODBWorker($connect = true)
    {
        static $rodbworker;
        
        // if CQRS read slave is not configured
        if (!defined("RO_DB_SERVER") ||
            !defined("RO_DB_NAME") ||
            !defined("RO_DB_USER") ||
            !defined("RO_DB_PASSWORD") ||
            !defined("RO_DB_PREFIX")
        ) {
            return self::getDBWorker($connect);
        }
        
        if (empty($rodbworker)) {
            $rodbworker = self::getClassInstance("DBWorker");
        }
        
        if (!$rodbworker) {
            return null;
        }
        
        if (!$rodbworker->is_extension_installed()) {
            MessageHandler::setError(sprintf(text("ErrDbExtenstionNotInstalled"), $rodbworker->get_extension_name(), $rodbworker->get_rdbms_name()));
            return null;
        }
        
        // do not connect, only object required
        // user will do connect by itself
        if (!$connect) {
            $rodbworker->clear_messages();
            return $rodbworker;
        }
        
        // instance already connected
        if ($rodbworker->is_connected()) {
            $rodbworker->clear_messages();
            return $rodbworker;
        }
        
        // try to connect only if first time
        // if onnection alredy tried and failed
        // do not try again within one request
        if ($rodbworker->get_last_error_id() != "") {
            return false;
        }
        
        if ($rodbworker->connect(RO_DB_SERVER, RO_DB_NAME, RO_DB_USER, RO_DB_PASSWORD)) {
            return $rodbworker;
        }
        
        if ($rodbworker->get_last_error_id() == "db_err") {
            MessageHandler::setError(text("ErrDbInaccessible"),
                sprintf(text("ErrDbConnNoDB"), RO_DB_NAME)
            );
        } elseif ($rodbworker->get_last_error_id() == "conf_err") {
            MessageHandler::setError(text("ErrNoDBConfig"));
        } elseif ($rodbworker->get_last_error_id() == "conn_err") {
            MessageHandler::setError(text("ErrDbInaccessible"),
                sprintf(text("ErrDbConnNoAccess"), RO_DB_SERVER, RO_DB_USER)
            );
        }
        
        return false;
    } // getRODBWorker
    //-----------------------------------------------------------------
    /**
     * @returns DBWorker
     */
    static function getSRDBWorker($connect = true)
    {
        static $rsdbworker;
        
        // if CQRS read slave is not configured
        if (!defined("SR_DB_SERVER") ||
            !defined("SR_DB_NAME") ||
            !defined("SR_DB_USER") ||
            !defined("SR_DB_PASSWORD") ||
            !defined("SR_DB_PREFIX")
        ) {
            return self::getDBWorker($connect);
        }
        
        if (empty($rsdbworker)) {
            $rsdbworker = self::getClassInstance("DBWorker");
        }
        
        if (!$rsdbworker) {
            return null;
        }
        
        if (!$rsdbworker->is_extension_installed()) {
            MessageHandler::setError(sprintf(text("ErrDbExtenstionNotInstalled"), $rsdbworker->get_extension_name(), $rsdbworker->get_rdbms_name()));
            return null;
        }
        
        // do not connect, only object required
        // user will do connect by itself
        if (!$connect) {
            $rsdbworker->clear_messages();
            return $rsdbworker;
        }
        
        // instance already connected
        if ($rsdbworker->is_connected()) {
            $rsdbworker->clear_messages();
            return $rsdbworker;
        }
        
        // try to connect only if first time
        // if onnection alredy tried and failed
        // do not try again within one request
        if ($rsdbworker->get_last_error_id() != "") {
            return false;
        }
        
        if ($rsdbworker->connect(SR_DB_SERVER, SR_DB_NAME, SR_DB_USER, SR_DB_PASSWORD)) {
            return $rsdbworker;
        }
        
        if ($rsdbworker->get_last_error_id() == "db_err") {
            MessageHandler::setError(text("ErrDbInaccessible"),
                sprintf(text("ErrDbConnNoDB"), SR_DB_NAME)
            );
        } elseif ($rsdbworker->get_last_error_id() == "conf_err") {
            MessageHandler::setError(text("ErrNoDBConfig"));
        } elseif ($rsdbworker->get_last_error_id() == "conn_err") {
            MessageHandler::setError(text("ErrDbInaccessible"),
                sprintf(text("ErrDbConnNoAccess"), SR_DB_SERVER, SR_DB_USER)
            );
        }
        
        return false;
    } // getSRDBWorker
    //-----------------------------------------------------------------
    /**
     * @returns DBWorker
     */
    static function getDBWorker($connect = true)
    {
        static $dbworker;
        
        if (empty($dbworker)) {
            $dbworker = self::getClassInstance("DBWorker");
        }
        
        if (!$dbworker) {
            return null;
        }
        
        if (!$dbworker->is_extension_installed()) {
            MessageHandler::setError(sprintf(text("ErrDbExtenstionNotInstalled"), $dbworker->get_extension_name(), $dbworker->get_rdbms_name()));
            return null;
        }
        
        // do not connect, only object required
        // user will do connect by itself
        if (!$connect) {
            $dbworker->clear_messages();
            return $dbworker;
        }
        
        // instance already connected
        if ($dbworker->is_connected()) {
            $dbworker->clear_messages();
            return $dbworker;
        }
        
        if (!defined("DB_SERVER") ||
            !defined("DB_NAME") ||
            !defined("DB_USER") ||
            !defined("DB_PASSWORD") ||
            !defined("DB_PREFIX")
        ) {
            MessageHandler::setError(text("ErrNoDBConfig"));
            return false;
        }
        
        // try to connect only if first time
        // if onnection alredy tried and failed
        // do not try again within one request
        if ($dbworker->get_last_error_id() != "") {
            return false;
        }
        
        if ($dbworker->connect(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD)) {
            return $dbworker;
        }
        
        if ($dbworker->get_last_error_id() == "db_err") {
            MessageHandler::setError(text("ErrDbInaccessible"),
                sprintf(text("ErrDbConnNoDB"), DB_NAME)
            );
        } elseif ($dbworker->get_last_error_id() == "conf_err") {
            MessageHandler::setError(text("ErrNoDBConfig"));
        } elseif ($dbworker->get_last_error_id() == "conn_err") {
            MessageHandler::setError(text("ErrDbInaccessible"),
                sprintf(text("ErrDbConnNoAccess"), DB_SERVER, DB_USER)
            );
        }
        
        return false;
    } // getDBWorker
    
    //-----------------------------------------------------------------
    static function getSessionCookiePath()
    {
        if (defined('HOME_DIRECTORY') && HOME_DIRECTORY != '') {
            $path = HOME_DIRECTORY;
        } else {
            $path = ini_get("session.cookie_path");
        }
    
        $path = rtrim($path, "/");
        
        if (empty($path)) {
            $path = "/";
        }
        
        return $path;
    } // getSessionCookiePath
    
    //-----------------------------------------------------------------
    static function sendJSON(&$response)
    {
        echo array_to_json($response);
    } // sendJSON
    
    //-----------------------------------------------------------------
    static function generateHashOld($text, $salt)
    {
        return crypt($text, $salt);
    } // generateHashOld
    
    //-----------------------------------------------------------------
    static function generateHash($text, $salt)
    {
        return hash_hmac('ripemd160', $text, $salt);
    } // generateHash
    
    //-----------------------------------------------------------------
    static function generateReadmarker()
    {
        return base64_encode(md5(time() . session_id()));
    } // generateReadmarker
    
    //-----------------------------------------------------------------
    static function generateSessionHashCode()
    {
        return md5(rand(100000, 900000));
    } // generateSessionHashCode
    //-----------------------------------------------------------------
} // System
//-------------------------------------------------------------------
?>