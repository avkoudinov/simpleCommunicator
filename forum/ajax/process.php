<?php
//-----------------------------------------------------------------------
session_set_cookie_params(0, str_replace("ajax/" . basename($_SERVER["PHP_SELF"]), "", $_SERVER["PHP_SELF"]));
require_once "../include/session_start_inc.php";

$ajax_processing = true;
require_once "../include/general_inc.php";
//-----------------------------------------------------------------------
if (detect_bot(val_or_empty($_SERVER["HTTP_USER_AGENT"])) != "") {
    exit;
}
//-----------------------------------------------------------------------
$response = array();
$response['success'] = false;
$show_messages = true;
//-----------------------------------------------------------------------
if (!empty($maintenance_until) && empty($_SESSION["admdebug"])) {
    MessageHandler::setWarning(sprintf(text("MaintenanceComment"), $maintenance_until, $time_zone_name));
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
} //---------------------------------------------------------------------
elseif (!reqvar_empty("user_logged") && !$fmanager->is_logged_in()) {
    $report_id = time() . "-" . rand(1000, 9999);
    if (!empty($_GET) || !empty($_POST)) {
        dump_request($report_id);
        $response['send_empty_hash_report'] = $report_id;
    }

    MessageHandler::setError(text("ErrSessionExpired"));
    
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
} //---------------------------------------------------------------------
elseif (!$fmanager->check_hash()) {
    $report_id = time() . "-" . rand(1000, 9999);
    if (!empty($_GET) || !empty($_POST)) {
        dump_request($report_id);
        $response['send_empty_hash_report'] = $report_id;
    }
    
    MessageHandler::setError(text("ErrWrongHashCode"));
    
    MessageHandler::addMessagesToResponse($response);
    System::sendJSON($response);
    exit;
} //---------------------------------------------------------------------
elseif (!reqvar_empty("clear_profile_data")) {
    $response['success'] = $fmanager->clear_profile_data();
} //---------------------------------------------------------------------
elseif (!reqvar_empty("switch_skin")) {
    $response['success'] = $fmanager->switch_skin(reqvar("switch_skin"));
} //---------------------------------------------------------------------
elseif (!reqvar_empty("verify_password")) {
    $response['success'] = $fmanager->verify_password(reqvar("fid"), reqvar("password"));
    
    if ($response['success']) {
        $show_messages = false;
        $response['target_url'] = val_or_empty($_SESSION["last_url_askpwd"]);
        if (empty($response['target_url'])) {
            $response['target_url'] = "forums.php";
        }
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("moderate_user")) {
    if (!$fmanager->is_moderator()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->moderate_user(reqvar("uid"));
        if ($response['success']) {
            $show_messages = false;
        }
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("load_auto_saved")) {
    $response['message'] = "";
    $response['success'] = $fmanager->load_auto_saved_message(reqvar("topic"), $response['message']);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("install")) {
    $response['success'] = $fmanager->install();
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("moderate_ip")) {
    if (!$fmanager->is_moderator()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->moderate_ip(reqvar("ip"), reqvar("author"));
        if ($response['success']) {
            $show_messages = false;
        }
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("delete_read_marker")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->delete_read_marker(reqvar("read_marker"));
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("forum_action")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        switch (reqvar("forum_action")) {
            case "open":
                $response['success'] = $fmanager->open_close_forum(reqvar("forum"), "0");
                break;
            
            case "close":
                $response['success'] = $fmanager->open_close_forum(reqvar("forum"), "1");
                break;
            
            case "delete":
                $response['success'] = $fmanager->delete_restore_forum(reqvar("forum"), "1");
                break;
            
            case "restore":
                $response['success'] = $fmanager->delete_restore_forum(reqvar("forum"), "0");
                break;
        }
    }
    
    if ($response['success']) {
        $show_messages = false;
        $response['target_url'] = "forums.php";
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("ignore_action")) {
    $ignore_action = reqvar("ignore_action");
    if ($ignore_action == "put_guest_to_ignore_list" || $ignore_action == "remove_guest_from_ignore_list") {
        $response['success'] = $fmanager->do_guest_ignore_action(reqvar("guest_name"), reqvar("ignore_action"), $response);
    } else {
        $response['success'] = $fmanager->do_ignore_action(reqvar("uid"), reqvar("ignore_action"), $response);
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("profile_hide_action")) {
    $hide_action = reqvar("profile_hide_action");
    if ($hide_action == "hide_user_profile" || $hide_action == "open_user_profile") {
        $response['success'] = $fmanager->do_profile_hide_action(reqvar("uid"), reqvar("profile_hide_action"), $response);
    } elseif ($hide_action == "hide_guest_profile" || $hide_action == "open_guest_profile") {
        $response['success'] = $fmanager->do_guest_profile_hide_action(reqvar("guest_id"), reqvar("profile_hide_action"), $response);
    } elseif ($hide_action == "delete_avatar") {
        $response['target_url'] = "";
    
        if (!$fmanager->is_admin() && !$fmanager->global_ban_allowed()) {
            MessageHandler::setError(text("ErrActionNotAllowed"));
        } else {
            $response['success'] = $fmanager->delete_guest_avatar_by_guest_id(reqvar("guest_name"), reqvar("guest_id"));
        }
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("subscribe_action")) {
    if (!$fmanager->is_logged_in()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } elseif ($fmanager->is_master_admin()) {
        MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    } else {
        $response['success'] = $fmanager->do_user_subscribe_action(reqvar("uid"), reqvar("user_name"), reqvar("subscribe_action"), $response);
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("unsubscribe_from_authors")) {
    if (!$fmanager->is_logged_in()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } elseif ($fmanager->is_master_admin()) {
        MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    } else {
        $authors = array();
        if (!empty($_REQUEST["authors"])) {
            $authors = $_REQUEST["authors"];
        }
        
        $response['success'] = $fmanager->unsubscribe_from_authors($authors);
        if ($response['success']) {
            $show_messages = false;
            $response['target_url'] = "subscription.php";
        }
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("delete_user_rates")) {
    if (!$fmanager->is_moderator()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->delete_user_rates(reqvar("uid"));
        if ($response['success']) {
            $show_messages = false;
        }
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("export_import_action")) {
    switch (reqvar("export_import_action")) {
        case "export":
            $response['success'] = $fmanager->export_profile_data();
            break;
        
        case "import":
            $response['success'] = $fmanager->import_profile_data();
            if ($response['success']) {
                $show_messages = false;
            }
            break;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("mark_events_done")) {
    $response['success'] = $fmanager->mark_events_done();
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("set_event_done")) {
    $response['success'] = $fmanager->set_event_done(reqvar("event"));
} //---------------------------------------------------------------------
elseif (!reqvar_empty("forum_user_action")) {
    $response['target_url'] = "";
    
    $response['success'] = $fmanager->do_forum_user_action(reqvar("forum"), reqvar("forum_user_action"), $response);
    
    if ($response['success'] && !empty($response['target_url'])) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("mark_read_action")) {
    switch (reqvar("mark_read_action")) {
        case "mark_subscriptions_read":
            $authors = array();
            if (!empty($_REQUEST["authors"])) {
                $authors = $_REQUEST["authors"];
            }
            
            $response['success'] = $fmanager->mark_subscriptions_read($authors);
            if ($response['success']) {
                MessageHandler::setInfo(text("MsgAuthorsMarkedRead"));
                $show_messages = false;
                $response['target_url'] = "subscription.php";
            }
            break;
        
        case "mark_forums_read":
            if (empty($_REQUEST["forums"])) {
                $response['success'] = $fmanager->mark_forums_read();
            } else {
                $response['success'] = $fmanager->mark_forum_read("");
            }

            if ($response['success']) {
                $show_messages = false;
                $response['target_url'] = "forums.php";
            }
            break;
        
        case "mark_forum_read":
            if (empty($_REQUEST["topics"])) {
                $response['success'] = $fmanager->mark_forum_read(reqvar("forum"));
            } else {
                $response['success'] = $fmanager->mark_topics_read("", reqvar("forum"));
            }
            
            if ($response['success']) {
                $show_messages = false;
                
                $response["target_url"] = "forum.php?fid=" . ($fmanager->get_private_forum_id() == reqvar("forum") ? "private" : reqvar("forum"));
                if (!reqvar_empty("fpage")) {
                    $response["target_url"] .= "&fpage=" . reqvar("fpage");
                }
            }
            break;
        
        case "mark_topic_read":
            // no reloading, target_url empty
            $response['success'] = $fmanager->mark_topics_read(reqvar("topic"), "");
            break;
        
        case "mark_topic_unread":
            // no reloading, target_url empty
            $response['success'] = $fmanager->mark_topic_unread(reqvar("start_post"), $response);
            break;
        
        case "mark_favourites_read":
            $response['success'] = $fmanager->mark_topics_read("", -1);
            if ($response['success']) {
                $show_messages = false;
                $response['target_url'] = "favourites.php";
                if (!reqvar_empty("fpage")) {
                    $response["target_url"] .= "?fpage=" . reqvar("fpage");
                }
            }
            break;
        
        case "mark_search_read":
            if (!empty($_REQUEST["topics"])) {
                $response['success'] = $fmanager->mark_topics_read("", "");
            }
            
            if ($response['success']) {
                $show_messages = false;
            }
            break;
        
        case "mark_new_read":
            $response['success'] = $fmanager->mark_topics_read(-2, reqvar("fid"));
            if ($response['success']) {
                $show_messages = false;
                $response['target_url'] = "new_messages.php";
                $fid = reqvar("fid");
                if (!empty($fid)) {
                    switch ($fid) {
                        case -1:
                            $fid = "favourites";
                            break;
                        case -2:
                            $fid = "my_topics";
                            break;
                        case -3:
                            $fid = "my_part_topics";
                            break;
                        case $fmanager->get_private_forum_id():
                            $fid = "private";
                            break;
                    }
                    
                    $response["target_url"] .= "?fid=" . $fid;
                }
                if (!reqvar_empty("fpage")) {
                    if ($response['target_url'] == "new_messages.php") {
                        $response['target_url'] .= "?";
                    } else {
                        $response['target_url'] .= "&";
                    }
                    
                    $response["target_url"] .= "fpage=" . reqvar("fpage");
                }
            }
            break;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("topic_action")) {
    $response['target_url'] = reqvar("current_url");
    
    if (empty($response['target_url'])) {
        $response['target_url'] = val_or_empty($_SESSION["last_url"]);
    }
    if (empty($response['target_url'])) {
        $response['target_url'] = "forums.php";
    }
    
    switch (reqvar("topic_action")) {
        case "citate_message":
            $response['target_url'] = "";
            $response['success'] = $fmanager->citate_message(reqvar("post"), $response);
            break;
        
        case "load_version":
            $response['target_url'] = "";
            $response['success'] = $fmanager->load_version(reqvar("post"), reqvar("version"), $response);
            break;
        
        case "comment_message":
            $response['target_url'] = "";
            if (reqvar("comment_mode") == "complain") {
                $response['success'] = $fmanager->complain_message(reqvar("pid"));
            }
            if (reqvar("comment_mode") == "warn") {
                $response['success'] = $fmanager->warn_message(reqvar("pid"), $response);
            }
            break;
        
        case "edit_message":
            $response['target_url'] = "";
            $response['success'] = $fmanager->get_message_for_edit(reqvar("post"), $response);
            break;
        
        case "add_remove_private_members":
            $response['success'] = $fmanager->add_remove_private_members(reqvar("topic"));
            
            if ($response['success']) {
                $show_messages = false;
                
                $response['target_url'] = "topic.php?fid=" . ($fmanager->get_private_forum_id() == reqvar("forum") ? "private" : reqvar("forum"));
                if (!reqvar_empty("fpage")) {
                    $response["target_url"] .= "&fpage=" . reqvar("fpage");
                }
                $response["target_url"] .= "&tid=" . reqvar("topic");
                if (!reqvar_empty("tpage")) {
                    $response["target_url"] .= "&tpage=" . reqvar("tpage");
                }
            }
            break;
        
        case "block_unblock_topic_users":
            $response['success'] = $fmanager->block_unblock_topic_users(reqvar("topic"));
            
            if ($response['success']) {
                $show_messages = false;
                
                $response['target_url'] = "topic.php?fid=" . ($fmanager->get_private_forum_id() == reqvar("forum") ? "private" : reqvar("forum"));
                if (!reqvar_empty("fpage")) {
                    $response["target_url"] .= "&fpage=" . reqvar("fpage");
                }
                $response["target_url"] .= "&tid=" . reqvar("topic");
                if (!reqvar_empty("tpage")) {
                    $response["target_url"] .= "&tpage=" . reqvar("tpage");
                }
            }
            break;
        
        case "leave_topic":
            $response['success'] = $fmanager->leave_topic(reqvar("topic"));
            
            if ($response['success']) {
                $show_messages = false;
                $response['target_url'] = "forum.php?fid=private";
            }
            break;
        
        case "delete_topic":
        case "restore_topic":
        case "open_topic":
        case "close_topic":
        case "pin_topic":
        case "unpin_topic":
        case "allow_guests":
        case "disallow_guests":
        case "profiled_topic_on":
        case "profiled_topic_off":
        case "blog_topic_on":
        case "blog_topic_off":
            $response['success'] = $fmanager->do_topic_action(reqvar("topic"), reqvar("topic_action"));
            
            if ($response['success']) {
                $show_messages = false;
                
                if (reqvar("topic_action") == "delete_topic") {
                    $response["target_url"] = "forum.php?fid=" . ($fmanager->get_private_forum_id() == reqvar("forum") ? "private" : reqvar("forum"));
                    if (!reqvar_empty("fpage")) {
                        $response["target_url"] .= "&fpage=" . reqvar("fpage");
                    }
                } else {
                    $response['target_url'] = "topic.php?fid=" . ($fmanager->get_private_forum_id() == reqvar("forum") ? "private" : reqvar("forum"));
                    if (!reqvar_empty("fpage")) {
                        $response["target_url"] .= "&fpage=" . reqvar("fpage");
                    }
                    $response["target_url"] .= "&tid=" . reqvar("topic");
                    if (!reqvar_empty("tpage")) {
                        $response["target_url"] .= "&tpage=" . reqvar("tpage");
                    }
                }
            }
            break;
        
        case "move_topic":
            if (!reqvar_empty("topic")) {
                $_REQUEST["topics"][] = reqvar("topic");
            }
            
            $response['success'] = $fmanager->move_topics(reqvar("target_forum"));
            
            if ($response['success']) {
                $show_messages = false;
                $response["target_url"] = "forum.php?fid=" . ($fmanager->get_private_forum_id() == reqvar("forum") ? "private" : reqvar("forum"));
                if (!reqvar_empty("fpage")) {
                    $response["target_url"] .= "&fpage=" . reqvar("fpage");
                }
            }
            break;
        
        case "move_posts":
        case "move_posts_from":
            $target_topic = reqvar("target_topic");
            $response['success'] = $fmanager->move_posts(reqvar("topic_action"), $target_topic, reqvar("new_topic"), $response);
            
            if (!reqvar_empty("in_search")) {
                $response['target_url'] = "";
            }
            
            if ($response['success'] && reqvar_empty("in_search")) {
                $show_messages = false;
            }
            break;
        
        case "merge_topic":
            if (!reqvar_empty("topic")) {
                $_REQUEST["topics"][] = reqvar("topic");
            }
            
            // target_url is set in the function
            $response['success'] = $fmanager->merge_topics(reqvar("target_topic"), reqvar("new_topic"), $response);
            
            if ($response['success']) {
                $show_messages = false;
            }
            break;
        
        case "merge_topics":
            // target_url is set in the function
            $response['success'] = $fmanager->merge_topics(reqvar("target_topic"), reqvar("new_topic"), $response);
            
            if ($response['success']) {
                $show_messages = false;
            }
            break;
        
        case "move_topics":
            $response['success'] = $fmanager->move_topics(reqvar("target_forum"));
            
            if ($response['success']) {
                $show_messages = false;
                $response['target_url'] = "forum.php?fid=" . reqvar("return_forum");
                if (!reqvar_empty("fpage")) {
                    $response["target_url"] .= "&fpage=" . reqvar("fpage");
                }
            }
            break;
        
        case "delete_topics":
        case "restore_topics":
        case "open_topics":
        case "close_topics":
            $response['success'] = $fmanager->do_topic_bulk_action(reqvar("topic_action"));
            
            if ($response['success']) {
                $show_messages = false;
                $response['do_reload'] = 1;
            }
            break;
        
        case "bulk_add_to_ignored":
        case "bulk_remove_from_ignored":
            $response['success'] = $fmanager->bulk_add_remove_ignored(reqvar("topic_action"));
            
            if ($response['success']) {
                $show_messages = false;
            }
            break;

        case "bulk_remove_from_favourites":
            $response['success'] = $fmanager->remove_from_favourites();
            if ($response['success']) {
                $show_messages = false;
                $response['target_url'] = "favourites.php";
                if (!reqvar_empty("fpage")) {
                    $response["target_url"] .= "?fpage=" . reqvar("fpage");
                }
            }
            break;
        
        case "subscribe":
        case "unsubscribe":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->do_topic_user_action(reqvar("topic"), reqvar("topic_action"), $response);
            }
            break;
        
        case "pin_user_topic":
        case "unpin_user_topic":
        case "add_to_favourites":
        case "remove_from_favourites":
        case "add_to_ignored":
        case "remove_from_ignored":
        case "publish":
            $response['target_url'] = "";
            
            $response['success'] = $fmanager->do_topic_user_action(reqvar("topic"), reqvar("topic_action"), $response);
            if ($response['success'] && reqvar("topic_action") == "publish") {
                $show_messages = false;
            }
            break;
        
        case "reset_rating":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->reset_rating(reqvar("post"), $response);
            }
            break;
        
        case "rate_post":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->rate_post(reqvar("post"), reqvar("rating"), $response);
            }
            break;
        
        case "delete_restore_attachment":
            $response['target_url'] = "";
            $response['success'] = $fmanager->delete_restore_attachment(reqvar("attachment"), reqvar("nr"));
            break;
        
        case "convert_to_thematic":
        case "convert_to_comment":
        case "convert_to_adult":
        case "convert_to_nonadult":
            $response['success'] = $fmanager->convert_posts(reqvar("topic_action"));
            
            $response['target_url'] = "";
            break;
        
        case "delete_post":
        case "restore_post":
            $response['success'] = $fmanager->delete_restore_posts(reqvar("topic_action"));
            
            $response['target_url'] = "";
            break;
        
        case "pin_post":
        case "unpin_post":
            $response['success'] = $fmanager->pin_unpin_post(reqvar("post"), reqvar("topic_action"), $response);
            
            $response['target_url'] = "";
            break;
        
        case "toggle_post_tag":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->toggle_post_tag(reqvar("post"), reqvar("tag"), $response);
            }
            break;
        
        case "delete_tags":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->delete_tags($response);
            }
            break;
        
        case "add_new_tag":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->add_new_tag(reqvar("post"), reqvar("new_tag"), $response);
            }
            break;
        
        case "add_new_tag2":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->add_new_tag2(reqvar("new_tag"), $response);
            }
            break;
        
        case "edit_tag":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->edit_tag(reqvar("tgid"), reqvar("tag_name"), $response);
            }
            break;
        
        case "merge_tags":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->merge_tags(reqvar("tgid"), reqvar("tag_name"), $response);
            }
            break;
        
        case "add_post_to_favourites":
        case "remove_post_from_favourites":
            $response['target_url'] = "";
            $response['success'] = $fmanager->add_remove_post_favourites(reqvar("post"), reqvar("topic_action"), $response);
            break;
        
        case "subscribe_to_post":
        case "unsubscribe_from_post":
            $response['target_url'] = "";
            
            if (!$fmanager->is_logged_in()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } elseif ($fmanager->is_master_admin()) {
                MessageHandler::setWarning(text("MsgMasterAdminWarning"));
            } else {
                $response['success'] = $fmanager->subscribe_unsubscribe_post(reqvar("post"), reqvar("topic_action"), $response);
            }
            break;
        
        case "add_attachment_to_favourites":
        case "remove_attachment_from_favourites":
            $response['target_url'] = "";
            
            $response['success'] = $fmanager->add_remove_attachment_favourites(reqvar("id"), reqvar("topic_action"), $response);
            break;
        
        case "delete_posts_in_topic":
        case "restore_posts_in_topic":
        case "restore_posts_in_topic_from":
            if (!$fmanager->is_admin() && !$fmanager->is_forum_moderator(reqvar("forum")) && !$fmanager->is_topic_moderator(reqvar("topic"))) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } else {
                $response['success'] = $fmanager->bulk_delete_restore_posts(reqvar("post"), reqvar("topic_action"), $response);
            }
            if ($response['success']) {
                $show_messages = false;
            }
            break;
        case "delete_posts_in_forum":
            if (!$fmanager->is_admin() && !$fmanager->is_forum_moderator(reqvar("forum"))) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } else {
                $response['success'] = $fmanager->bulk_delete_restore_posts(reqvar("post"), reqvar("topic_action"), $response);
            }
            if ($response['success']) {
                $show_messages = false;
            }
            break;
        case "delete_last_N_posts":
            if (!$fmanager->is_admin() && !$fmanager->is_forum_moderator(reqvar("forum"))) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } else {
                $response['success'] = $fmanager->bulk_delete_restore_posts(reqvar("post"), reqvar("topic_action"), $response);
            }
            if ($response['success']) {
                $show_messages = false;
            }
            break;
        case "delete_all_posts":
            if (!$fmanager->is_admin()) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } else {
                $response['success'] = $fmanager->bulk_delete_restore_posts(reqvar("post"), reqvar("topic_action"), $response);
            }
            if ($response['success']) {
                $show_messages = false;
            }
            break;
        
        case "block_user_in_topic":
        case "unblock_user_in_topic":
            $response['target_url'] = "";
            $response['success'] = $fmanager->block_unblock_user_in_topic(reqvar("topic_action"), reqvar("topic"), reqvar("user"), $response);
            break;
        
        case "make_topic_moderator":
        case "revoke_topic_moderator":
            $response['target_url'] = "";
            
            if (!$fmanager->is_admin() && !$fmanager->is_forum_moderator(reqvar("forum"))) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } else {
                $response['success'] = $fmanager->make_revoke_topic_moderator(reqvar("post"), reqvar("topic_action"), $response);
            }
            break;
        
        case "delete_avatar":
            $response['target_url'] = "";
            
            if (!$fmanager->is_admin() && !$fmanager->global_ban_allowed() && !$fmanager->is_forum_moderator(reqvar("forum"))) {
                MessageHandler::setError(text("ErrActionNotAllowed"));
            } else {
                $response['success'] = $fmanager->delete_guest_avatar(reqvar("post"), $response);
            }
            break;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_forum")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->save_forum();
    }
    
    if ($response['success']) {
        $show_messages = false;
        $response['target_url'] = "forums.php";
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_white_ips")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->save_white_list_ips();
        
        if ($response['success']) {
            $show_messages = false;
        }
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("whitelist_ip")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['ips'] = "";
        $response['success'] = $fmanager->whitelist_ip($response['ips']);
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("tor_ip_action")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } elseif (reqvar("tor_ip_action") == "block_all" || reqvar("tor_ip_action") == "unblock_all") {
        $response['success'] = $fmanager->block_unblock_tor_ips(reqvar("tor_ip_action"));
        
        if ($response['success']) {
            $show_messages = false;
        }
    } elseif (reqvar("tor_ip_action") == "change_block_level") {
        $response['success'] = $fmanager->change_tor_ip_block_level(reqvar("ip"), reqvar("level"));
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("search_authors")) {
    $response['found_entries'] = array();
    $response['success'] = $fmanager->find_users(reqvar("lookup_string"), $response['found_entries'], text("MasterAdministrator"));
} //---------------------------------------------------------------------
elseif (!reqvar_empty("search_users")) {
    $response['found_entries'] = array();
    $response['success'] = $fmanager->find_users(reqvar("lookup_string"), $response['found_entries']);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("search_topics")) {
    $response['found_entries'] = array();
    
    $topic_name = preg_replace("/^\[#\d+]\\s+/", "", reqvar("lookup_string"));
    
    $response['success'] = $fmanager->find_topics($topic_name, $response['found_entries']);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("check_existing_topics")) {
    $response['found_entries'] = array();
    
    $response['success'] = $fmanager->find_existing_topics(reqvar("lookup_string"), reqvar("forum"), $response['found_entries']);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("search_moderated_topics")) {
    $response['found_topics'] = array();
    $response['success'] = $fmanager->find_moderated_topics(reqvar("topic_to_search"), reqvar("forum"), reqvar("merge_modus"), $response['found_topics']);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("search_moderated_users")) {
    $response['found_users'] = array();
    
    if (!$fmanager->is_moderator()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->find_moderated_users(reqvar("forum"), reqvar("start_date"), reqvar("hour"), reqvar("minute"), $response['found_users']);
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("bulk_delete")) {
    if (!$fmanager->is_moderator()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->bulk_delete_posts_by_users(reqvar("forum"), reqvar("start_date"), reqvar("hour"), reqvar("minute"));
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("change_password")) {
    if (!$fmanager->is_master_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->change_master_password();
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_settings")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->save_settings();
    }
    
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_forum_groups")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->save_forum_groups();
    }
    
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("send_message")) {
    $response['success'] = $fmanager->send_contact_message();
    
    if ($response['success']) {
        MessageHandler::setFocusElement("subject");
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("post_message")) {
    if (!reqvar_empty("edit_mode")) {
        $response['success'] = $fmanager->update_message($response);
    } else {
        $response['success'] = $fmanager->post_message($response);
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("preview_message")) {
    $response['success'] = $fmanager->preview_message($response);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("vote")) {
    $response['success'] = $fmanager->vote($response);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("cancel_vote")) {
    $response['success'] = $fmanager->cancel_vote($response);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("close_poll")) {
    $response['success'] = $fmanager->open_close_poll("close_poll", $response);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("open_poll")) {
    $response['success'] = $fmanager->open_close_poll("open_poll", $response);
} //---------------------------------------------------------------------
elseif (!reqvar_empty("register")) {
    $response['success'] = $fmanager->register();
    
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("do_login")) {
    $response['target_url'] = val_or_empty($_SESSION["last_url"]);
    
    if (empty($response['target_url'])) {
        $response['target_url'] = "forums.php";
    }
    
    $response['success'] = $fmanager->login($response['failed_login_count']);
    
    if ($response['success']) {
        if (!empty($_SESSION["last_url_asklogin"])) {
            $response['target_url'] = val_or_empty($_SESSION["last_url_asklogin"]);
            unset($_SESSION["last_url_asklogin"]);
        }
        
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("restore_password")) {
    $response['success'] = $fmanager->restore_password();
} //---------------------------------------------------------------------
elseif (!reqvar_empty("reset_password")) {
    $response['success'] = $fmanager->reset_password();
    
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_profile")) {
    $response['success'] = $fmanager->save_profile();
    
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_guest_profile")) {
    $response['success'] = $fmanager->save_guest_profile();
    
    if ($response['success']) {
        $show_messages = false;
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_notes")) {
    if (!$fmanager->is_logged_in()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } elseif ($fmanager->is_master_admin()) {
        MessageHandler::setWarning(text("MsgMasterAdminWarning"));
    } else {
        $response['success'] = $fmanager->save_notes($response, reqvar("uid"), reqvar("notes"));
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("request_activation")) {
    $response['success'] = $fmanager->request_activation();
} //---------------------------------------------------------------------
elseif (!reqvar_empty("save_user")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->save_user();
    }
    
    if ($response['success']) {
        $show_messages = false;
        $response['target_url'] = "edit_user.php?uid=" . reqvar("uid");
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("delete_user")) {
    if (!$fmanager->is_admin()) {
        MessageHandler::setError(text("ErrActionNotAllowed"));
    } else {
        $response['success'] = $fmanager->delete_user();
    }
    
    if ($response['success']) {
        $show_messages = false;
        $response['target_url'] = "users.php";
    }
} //---------------------------------------------------------------------
elseif (!reqvar_empty("show_user_email")) {
    $user_data = array();
    $response['success'] = $fmanager->get_user_data(reqvar("uid"), $user_data);
    
    if ($response['success']) {
        $response['user_email'] = $user_data["user_email"];
        if (!empty($response['hide_email'])) {
            $response['user_email'] = text("hidden");
        }
        
        $response['user_email'] = escape_html($response['user_email']);
    }
}
//-----------------------------------------------------------------------

//-----------------------------------------------------------------------
if (empty($response['success']) && !MessageHandler::errorsExist() && !MessageHandler::warningsExist())
{
    MessageHandler::setError(text("ErrNoValidCommand"));
}

if ($show_messages) {
    MessageHandler::addMessagesToResponse($response);
}
System::sendJSON($response);
//-----------------------------------------------------------------------
require_once "../include/final_inc.php";
//-----------------------------------------------------------------------
?>