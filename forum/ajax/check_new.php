<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";

$ajax_processing = true;
$background_activity = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
$response = array();
$response['success'] = false;
$response['protection_hash'] = val_or_empty($_SESSION["hash"]);
//-----------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"]))) {
    exit;
}
//-----------------------------------------------------------------------
if (empty($installed)) {
    System::sendJSON($response);
    exit;
}
//-----------------------------------------------------------------------
if (!empty($maintenance_until) && empty($_SESSION["admdebug"])) {
    System::sendJSON($response);
    exit;
} //-----------------------------------------------------------------------
elseif (!$fmanager->check_hash()) {
    System::sendJSON($response);
    exit;
}
//-----------------------------------------------------------------------

if (empty($_SESSION["turnoff_events"])) {
    $response['new_events_count'] = 0;
    $response['new_mod_events_count'] = 0;
    $response['success'] = $fmanager->check_new_events($response['new_events_count'], $response['new_mod_events_count']);
}

$response['success'] = $fmanager->calculate_new_messages(true /* no cache */);

if ($response['success']) {
    $tid = "";
    
    $response['topics_with_new_count'] = empty($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]);
    $response['favourites_with_new_count'] = empty($_SESSION["new_messages_info_cache"]["data"]["favourites"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["favourites"]);
    $response['my_topics_with_new_count'] = empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_topics"]);
    $response['my_part_topics_with_new_count'] = empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]);
    $response['private_topics_with_new_count'] = empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
    $response['subscription_authors_new_messages_count'] = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"];
    $response['subscription_authors_new_topics_count'] = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"];
    
    $response['forums_with_new']["private"] = $response['private_topics_with_new_count'];
    
    $topic_is_ignored = false;
    
    // we are in a topic
    if (!reqvar_empty("tid")) {
        $tid = reqvar("tid");
        
        //debug_message("---------------------------------");
        //debug_message("user: " . $fmanager->get_user_name());
        //debug_message($_SERVER["PHP_SELF"]);
        
        $response['new_messages_count'] = 0;
        if (isset($_SESSION["new_messages_info_cache"]["data"]["ignored_topics"][$tid])) {
            $response['new_messages_count'] = $_SESSION["new_messages_info_cache"]["data"]["ignored_topics"][$tid];
            // exclusion of the topic count not necessary because the ignored topics are not counted
            // moderated ignored topics fall into normal case and counted
            $topic_is_ignored = true;
        } elseif (isset($_SESSION["new_messages_info_cache"]["data"]["topics"][$tid])) {
            $response['new_messages_count'] = $_SESSION["new_messages_info_cache"]["data"]["topics"][$tid];
            //debug_message("new msg count for topic $tid taken from session (topics): " . $response['new_messages_count']);
            
            // we exclude the current topic from the count
            if ($response['topics_with_new_count'] > 0) {
                $response['topics_with_new_count']--;
            }
        } elseif (isset($_SESSION["new_messages_info_cache"]["data"]["private_topics"][$tid])) {
            $response['new_messages_count'] = $_SESSION["new_messages_info_cache"]["data"]["private_topics"][$tid];
            //debug_message("new msg count for topic $tid taken from session (private_topics): " . $response['new_messages_count']);
            
            // we exclude the current topic from the count of private topics of topics with new if it is not ignored
            if ($response['private_topics_with_new_count'] > 0) {
                $response['private_topics_with_new_count']--;
            }
        }
        
        // we exclude the current topic from the count of the favourites with new if it is not ignored
        if (!empty($_SESSION["new_messages_info_cache"]["data"]["favourites"][$tid]) && !$topic_is_ignored) {
            if (!empty($response['favourites_with_new_count'])) {
                $response['favourites_with_new_count']--;
            }
        }
        
        // we exclude the current topic from the count of the my topics with new if it is not ignored
        if (!empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"][$tid]) && !$topic_is_ignored) {
            if (!empty($response['my_topics_with_new_count'])) {
                $response['my_topics_with_new_count']--;
            }
        }
        
        // we exclude the current topic from the count of the my part. topics with new if it is not ignored
        if (!empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"][$tid]) && !$topic_is_ignored) {
            if (!empty($response['my_part_topics_with_new_count'])) {
                $response['my_part_topics_with_new_count']--;
            }
        }
    } // if in a topic
    
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["forums"])) {
        foreach ($_SESSION["new_messages_info_cache"]["data"]["forums"] as $fid => $topics) {
            $response['forums_with_new'][$fid] = count($topics);
            
            if (!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$fid])) {
                $response['not_preferred_forums'][$fid] = 1;
            }
            
            // we are in the topic exclude the current topic from the count of topics with new
            if (!empty($topics[$tid]) && !$topic_is_ignored) {
                if (!empty($response['forums_with_new'][$fid])) {
                    $response['forums_with_new'][$fid]--;
                }
            }
        }
    }
    
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["ignored_topics"])) {
        foreach ($_SESSION["new_messages_info_cache"]["data"]["ignored_topics"] as $tid => $cnt) {
            $response['topics_with_new'][$tid] = $cnt;
            
            $response['ignored_topics'][$tid] = 1;
        }
    }
    
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"])) {
        foreach ($_SESSION["new_messages_info_cache"]["data"]["private_topics"] as $tid => $cnt) {
            $response['topics_with_new'][$tid] = $cnt;
            
            $response['never_visited_topics'][$tid] = val_or_empty($_SESSION["new_messages_info_cache"]["data"]["never_visited_topics"][$tid]);
        }
    }
    
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["topics"])) {
        foreach ($_SESSION["new_messages_info_cache"]["data"]["topics"] as $tid => $cnt) {
            $response['topics_with_new'][$tid] = $cnt;
            
            $response['never_visited_topics'][$tid] = val_or_empty($_SESSION["new_messages_info_cache"]["data"]["never_visited_topics"][$tid]);
        }
    }
    
    // subscription handling
    
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["subscription_author_new_messages"])) {
        foreach ($_SESSION["new_messages_info_cache"]["data"]["subscription_author_new_messages"] as $author => $cnt) {
            $response['subscription_author_new_messages'][$author] = $cnt;
        }
    }
    
    if (!empty($_SESSION["new_messages_info_cache"]["data"]["subscription_author_new_topics"])) {
        foreach ($_SESSION["new_messages_info_cache"]["data"]["subscription_author_new_topics"] as $author => $cnt) {
            $response['subscription_author_new_topics'][$author] = $cnt;
        }
    }
} // if success

//-----------------------------------------------------------------------
System::sendJSON($response);
//-----------------------------------------------------------------------
require_once "../include/final_inc.php";
//-----------------------------------------------------------------------
?>