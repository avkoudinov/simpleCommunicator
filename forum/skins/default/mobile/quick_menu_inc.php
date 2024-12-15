<!-- BEGIN: header2 -->

<div class="header2" id="<?php echo($main_menu_id); ?>">
  <?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>
    <?php
    $display = "style='display:none'";
    if(!empty($private_topics_with_new_count)) $display = "";
    ?>
    <a href="forum.php?fid=private"><?php echo_html(text("PrivateTopicsShort")); ?></a><span class="new private_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=private"><span class='private_topics_with_new_count'><?php echo($private_topics_with_new_count); ?></span></a>]</span> |
    
    <?php if(empty($_SESSION["turnoff_events"])): 
     $display = "style='display:none'";
     if(!empty($new_events_count)) $display = "";
    ?>
    <a href="events.php"><?php echo_html(text("EventsShort")); ?></a><span class="new new_events_indicator" <?php echo($display); ?>>&nbsp;[<a href="events.php"><span class='new_events_count'><?php echo($new_events_count); ?></span></a>]</span> |
    <?php endif; ?>
  <?php endif; ?>


  <?php
  $display = "style='display:none'";
  if(!empty($topics_with_new_count)) $display = "";
  ?>
  <a rel="nofollow" href="new_messages.php"><?php echo_html(text("NewMessagesShort")); ?></a><span class="new topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php"><span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> |

  <a href="search.php?do_search=1&hot_topics=1"><?php echo_html(text("HotTopicsShort")); ?></a> |

  <?php
  $display = "style='display:none'";
  if(!empty($favourites_with_new_count)) $display = "";
  ?>
  <a href="favourites.php"><?php echo_html(text("FavouritesShort")); ?></a><span class="new favourites_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=favourites"><span class='favourites_with_new_count'><?php echo($favourites_with_new_count); ?></span></a>]</span>
  
  <?php if($fmanager->is_logged_in() && !$fmanager->is_master_admin()): ?>

    <?php if(!empty($_SESSION["subscribed_authors"])): ?>
    <?php
    $display = "style='display:none'";
    if(!empty($subscription_authors_new_messages_count)) $display = "";
    ?>
    | <a href="subscription.php"><?php echo_html(text("SubscriptionShort")); ?></a><span class="new subscription_new_indicator" <?php echo($display); ?>>&nbsp;[<a href="search.php?do_search=1&author=<?php echo(xrawurlencode(text("Subscription"))); ?>&author_mode=wrote_post&post_list=1&post_sort=desc&unseen=1&mark_read=1"><span class='subscription_authors_new_messages_count'><?php echo($subscription_authors_new_messages_count); ?></span></a>]</span>
    <?php endif; ?>

    <?php
    $display = "style='display:none'";
    if(!empty($my_topics_with_new_count)) $display = "";
    ?>
    | <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=created_topic"><?php echo_html(text("MyTopicsShort")); ?></a><span class="new my_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=my_topics"><span class='my_topics_with_new_count'><?php echo($my_topics_with_new_count); ?></span></a>]</span>

    / <a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=last_posts"><?php echo_html(text("MyMessagesShort2")); ?></a>

    / <a href="search_topic.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=last_replies"><?php echo_html(text("MyRepliesShort")); ?></a>

    <?php if(!empty($_SESSION["skin_properties"][$skin]["show_my_part_topics"])): ?>
    <?php
    $display = "style='display:none'";
    if(!empty($my_part_topics_with_new_count)) $display = "";
    ?>
    | <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=participating"><?php echo_html(text("ParticipatedTopicsShort")); ?></a><span class="new my_part_topics_with_new_indicator" <?php echo($display); ?>>&nbsp;[<a rel="nofollow" href="new_messages.php?fid=my_part_topics"><span class='my_part_topics_with_new_count'><?php echo($my_part_topics_with_new_count); ?></span></a>]</span>
    <?php endif; ?>
  
  <?php else: ?>

    <?php if($fmanager->get_user_name() != ""): ?>
    | <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=created_topic"><?php echo_html(text("MyTopics")); ?></a>

    / <a href="search.php?do_search=1&author=<?php echo_html(xrawurlencode($fmanager->get_user_name())); ?>&author_mode=last_posts"><?php echo_html(text("MyMessagesShort")); ?></a>
    <?php endif; ?>

  <?php endif; ?>
  
</div>

<!-- END: header2 -->
