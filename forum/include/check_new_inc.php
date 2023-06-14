<?php
// Caching approach is used to reduce wait time for the loading of the main content.
// When the main content is being loaded, the info about new messages is taken from the cache.
// Then, the site starts an ajax script for checking new every 20 seconds. 
// This script refreshes the cache and the info about new messages on the site. 

$fmanager->check_new_events($new_events_count, $new_mod_events_count);
$fmanager->calculate_new_messages();

$topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["visible_topics"]);
$favourites_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["favourites"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["favourites"]);
$my_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_topics"]);
$my_part_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["my_part_topics"]);
$private_topics_with_new_count = empty($_SESSION["new_messages_info_cache"]["data"]["private_topics"]) ? 0 : count($_SESSION["new_messages_info_cache"]["data"]["private_topics"]);
$subscription_authors_new_messages_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_messages_count"];
$subscription_authors_new_topics_count = empty($_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"]) ? 0 : $_SESSION["new_messages_info_cache"]["data"]["subscription_authors_new_topics_count"];

?>