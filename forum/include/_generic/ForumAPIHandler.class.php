<?php

class ForumAPIHandler
{
    //------------------------------------
    protected $api_manager;
    protected $forum_manager;
    protected $current_action = null;

    //------------------------------------
    function __construct()
    {
        $this->api_manager = ForumAPIManager::instance();
        $this->forum_manager = ForumManager::instance();
    }   
    //-----------------------------------------------------------------
    public function sendJsonResponse(&$response_data, $headers = [])
    {
        header('Content-type: application/json');
        
        if (!empty($headers)) {
            if (is_array($headers)) {
                foreach ($headers as $header) {
                    header($header);
                }
            }
        }
        
        echo json_encode($response_data, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    } // sendJsonResponse
    //------------------------------------
    function handleRequest($api_request)
    {
        mb_internal_encoding("UTF-8");
        //date_default_timezone_set("GMT");

        $response_data = array();
      
        try {
            $headers = getallheaders();
            
            $content_type = empty($headers["Content-Type"]) ? "" : $headers["Content-Type"];
            if (!preg_match("/application\/json.*/", $content_type)) {
                throw new ForumAPIException(sprintf("Content type 'application/json' is expected, got '%s'!", $content_type), ForumAPIException::ERR_CODE_INVALID_CONTENT_TYPE);
            }

            $request_data = trim(file_get_contents("php://input"));

            if (empty($request_data)) {
                throw new ForumAPIException("The request JSON is empty!", ForumAPIException::ERR_CODE_MISSING_REQUEST_DATA);
            }
            
            $request_data = json_decode($request_data, true);
            
            if (empty($request_data) && !is_array($request_data)) {
                throw new ForumAPIException(json_last_error_msg(), ForumAPIException::ERR_CODE_JSON_PARSE_ERROR);
            }
            
            // We agreed, that the method name is equal to the api request name.
            // So we can use reflexion instead of maitanining the switch.
            
            $robject = new \ReflectionObject($this);
            
            if ($api_request == "handleRequest" || !$robject->hasMethod($api_request)) {
                throw new ForumAPIException(sprintf("No handler is defined for the API request '%s'!", $api_request), ForumAPIException::ERR_CODE_SYSTEM_ERROR);
            }
            
            $rmethod = $robject->getMethod($api_request);
            
            if ($rmethod->isConstructor() || $rmethod->isDestructor() || !$rmethod->isPublic()) {
                throw new ForumAPIException(sprintf("No handler is defined for the API request '%s'!", $api_request), ForumAPIException::ERR_CODE_SYSTEM_ERROR);
            }
        
            set_language($request_data["language"] ?? "");
            
            $this->check_token($request_data, $response_data);
            
            $rmethod->invokeArgs($this, [&$request_data, &$response_data]);
            
            $response_data["result"] = "success";
        } catch (ForumAPIException $ex) {
            $response_data["result"] = "error";
            $response_data["errors"][] = array(
              "error_code" => $ex->getErrorCode(),
              "error_text" => $ex->getMessage()
            );
        } catch (\Exception $ex) {
            $response_data["result"] = "error";
            $response_data["errors"][] = array(
              "error_code" => ForumAPIException::ERR_CODE_SYSTEM_ERROR,
              "error_text" => $ex->getMessage()
            );
        }
      
        $this->sendJsonResponse($response_data);
    } // handleRequest
    //------------------------------------
    protected function check_token(&$request_data, &$response_data)
    {
        $this->api_manager->check_token($request_data["api_token"] ?? "");
    } // check_token
    //------------------------------------
    function get_forum_list(&$request_data, &$response_data)
    {
      $response_data["forum_list"] = [];
      $this->api_manager->get_forum_list($response_data["forum_list"]);
    } // innotour_statistics
    //------------------------------------
    function get_topic_list(&$request_data, &$response_data)
    {
      $response_data["topic_list"] = [];
      $this->api_manager->get_topic_list($response_data["topic_list"], $request_data);
    } // innotour_statistics
    //------------------------------------
    function get_post_list(&$request_data, &$response_data)
    {
      $response_data["post_list"] = [];
      $this->api_manager->get_post_list($response_data["post_list"], $request_data);
    } // get_post_list
    //------------------------------------
    function get_attachment(&$request_data, &$response_data)
    {
      $this->api_manager->get_attachment($request_data);
    } // get_post_list
    //------------------------------------
    function post_message(&$request_data, &$response_data)
    {
      $response_data["post"] = [];
      $this->api_manager->post_message($request_data, $response_data["post"]);
    } // get_post_list
} // ForumAPIHandler