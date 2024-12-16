<script>
var load_browser_stats_ajax = null;

function load_browser_stats(load_browser_stat_button)
{
  if(load_browser_stat_button) load_browser_stat_button.classList.add("member_search_button_active");

  if(!load_browser_stats_ajax)
  {
    load_browser_stats_ajax = new Forum.AJAX();

    load_browser_stats_ajax.timeout = TIMEOUT;

    load_browser_stats_ajax.beforestart = function() { break_check_new_messages(); };
    load_browser_stats_ajax.aftercomplete = function(error) { 
      activate_check_new_messages(); 
      check_new_messages();
    };

    load_browser_stats_ajax.onload = function(text, xml)
    {
      if(text.trim() == '') 
      {
        if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
        return;
      }

      try
      {
        var elm = document.getElementById('browser_statistics');
        if(elm) elm.innerHTML = text;
      }
      catch(err)
      {
      }

      if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
    };

    load_browser_stats_ajax.onerror = function(error, url, info)
    {
        if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
    };
  } // init ajax

  load_browser_stats_ajax.abort();
  load_browser_stats_ajax.resetParams();

  load_browser_stats_ajax.setPOST('hash', get_protection_hash());
  load_browser_stats_ajax.setPOST('user_logged', user_logged);
  load_browser_stats_ajax.setPOST('trace_sql', trace_sql);

  load_browser_stats_ajax.request("ajax/load_browser_stats.php<?php echo($query_string); ?>");

  return false;
}

var load_geo_stats_ajax = null;

function load_geo_stats(load_browser_stat_button)
{
  if(load_browser_stat_button) load_browser_stat_button.classList.add("member_search_button_active");

  if(!load_geo_stats_ajax)
  {
    load_geo_stats_ajax = new Forum.AJAX();

    load_geo_stats_ajax.timeout = TIMEOUT;

    load_geo_stats_ajax.beforestart = function() { break_check_new_messages(); };
    load_geo_stats_ajax.aftercomplete = function(error) { 
      activate_check_new_messages(); 
      check_new_messages();
    };

    load_geo_stats_ajax.onload = function(text, xml)
    {
      if(text.trim() == '') 
      {
        if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
        return;
      }

      try
      {
        var elm = document.getElementById('geo_statistics');
        if(elm) elm.innerHTML = text;
      }
      catch(err)
      {
      }

      if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
    };

    load_geo_stats_ajax.onerror = function(error, url, info)
    {
        if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
    };
  } // init ajax

  load_geo_stats_ajax.abort();
  load_geo_stats_ajax.resetParams();

  load_geo_stats_ajax.setPOST('hash', get_protection_hash());
  load_geo_stats_ajax.setPOST('user_logged', user_logged);
  load_geo_stats_ajax.setPOST('trace_sql', trace_sql);

  load_geo_stats_ajax.request("ajax/load_geo_stats.php<?php echo($query_string); ?>");

  return false;
}

var load_city_geo_stats_ajax = null;

function load_city_geo_stats(load_browser_stat_button)
{
  if(load_browser_stat_button) load_browser_stat_button.classList.add("member_search_button_active");

  if(!load_city_geo_stats_ajax)
  {
    load_city_geo_stats_ajax = new Forum.AJAX();

    load_city_geo_stats_ajax.timeout = TIMEOUT;

    load_city_geo_stats_ajax.beforestart = function() { break_check_new_messages(); };
    load_city_geo_stats_ajax.aftercomplete = function(error) { 
      activate_check_new_messages(); 
      check_new_messages();
    };

    load_city_geo_stats_ajax.onload = function(text, xml)
    {
      if(text.trim() == '') 
      {
        if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
        return;
      }

      try
      {
        var elm = document.getElementById('city_geo_statistics');
        if(elm) elm.innerHTML = text;
      }
      catch(err)
      {
      }

      if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
    };

    load_city_geo_stats_ajax.onerror = function(error, url, info)
    {
        if(load_browser_stat_button) load_browser_stat_button.classList.remove("member_search_button_active");
    };
  } // init ajax

  load_city_geo_stats_ajax.abort();
  load_city_geo_stats_ajax.resetParams();

  load_city_geo_stats_ajax.setPOST('hash', get_protection_hash());
  load_city_geo_stats_ajax.setPOST('user_logged', user_logged);
  load_city_geo_stats_ajax.setPOST('trace_sql', trace_sql);

  load_city_geo_stats_ajax.request("ajax/load_city_geo_stats.php<?php echo($query_string); ?>");

  return false;
}

function reload_statistics()
{
  var form = document.getElementById("statistics_filter_form");

  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  form.submit();
}

var load_total_rates_ajax = null;

function load_total_rates(load_total_rates_button)
{
  if(load_total_rates_button) load_total_rates_button.classList.add("member_search_button_active");
  
  if(!load_total_rates_ajax)
  {
    load_total_rates_ajax = new Forum.AJAX();

    load_total_rates_ajax.timeout = TIMEOUT;

    load_total_rates_ajax.beforestart = function() { break_check_new_messages(); };
    load_total_rates_ajax.aftercomplete = function(error) { 
      activate_check_new_messages(); 
      check_new_messages();
    };

    load_total_rates_ajax.onload = function(text, xml)
    {
      if(text.trim() == '') {
        if(load_total_rates_button) load_total_rates_button.classList.remove("member_search_button_active");
        return;
      }  

      try
      {
        var rates = document.getElementsByClassName('total_rates_area');
        for(var i = 0; i < rates.length; i++)
        {
          rates[i].innerHTML = text;
        }
      }
      catch(err)
      {
      }

      if(load_total_rates_button) load_total_rates_button.classList.remove("member_search_button_active");
    };

    load_total_rates_ajax.onerror = function(error, url, info)
    {
      if(load_total_rates_button) load_total_rates_button.classList.remove("member_search_button_active");
    };
  } // init ajax

  load_total_rates_ajax.abort();
  load_total_rates_ajax.resetParams();

  load_total_rates_ajax.setPOST('hash', get_protection_hash());
  load_total_rates_ajax.setPOST('user_logged', user_logged);
  load_total_rates_ajax.setPOST('trace_sql', trace_sql);
  
  load_total_rates_ajax.request("ajax/load_total_rates.php<?php echo($query_string); ?>");

  return false;
}
</script>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

<?php if(!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])): ?>
<span class="closed">[<?php echo_html(empty($_SESSION["ip_block_time_left"]) ? text("ip_blocked") : sprintf(text("ip_blocked_until"), $_SESSION["ip_block_time_left"])); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["activated"])): ?>
<span class="closed">[<?php echo_html(text("notActivated")); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["approved"])): ?>
<span class="closed">[<?php echo_html(text("notApproved")); ?>]</span>
<?php elseif(!empty($_SESSION["blocked"])): 
$self_blocked_class = "";
if(val_or_empty($_SESSION["self_blocked"]) == 1) $self_blocked_class = "self_blocked";
elseif(val_or_empty($_SESSION["self_blocked"]) == 2) $self_blocked_class = "author_dead";
?>
<span class="closed <?php echo($self_blocked_class); ?>">[<?php echo_html(empty($_SESSION["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $_SESSION["block_time_left"])); ?>]</span>
<?php endif; ?>

/ <a href="load_statistics.php"><?php echo_html(text("LoadStatistics")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("Statistics")); ?></span> 
</div>

<!-- END: forum_bar -->

<div class="body_wrapper">

<h3 class="profile_caption"><?php echo_html(text("ActualStatistics")); ?></h3>

<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Index")); ?></th>
<th><?php echo_html(text("Previous24")); ?></th>
<th><?php echo_html(text("Last24Hours")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("NewUsers")); ?></td>
<td><?php if(isset($_SESSION["yesterday"]["new_users"])) echo_html(format_number($_SESSION["yesterday"]["new_users"])); ?></td>
<td><?php if(isset($_SESSION["today"]["new_users"])) echo_html(format_number($_SESSION["today"]["new_users"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("OnlineMembers")); ?></td>
<td><?php if(isset($_SESSION["yesterday"]["online_users"])) echo_html(format_number($_SESSION["yesterday"]["online_users"])); ?></td>
<td><?php if(isset($_SESSION["today"]["online_users"])) echo_html(format_number($_SESSION["today"]["online_users"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("MessagesCount")); ?></td>
<td><?php if(isset($_SESSION["yesterday"]["total_posts"])) echo_html(format_number($_SESSION["yesterday"]["total_posts"])); ?></td>
<td><?php if(isset($_SESSION["today"]["total_posts"])) echo_html(format_number($_SESSION["today"]["total_posts"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("TopicsCount")); ?></td>
<td><?php if(isset($_SESSION["yesterday"]["total_topics"])) echo_html(format_number($_SESSION["yesterday"]["total_topics"])); ?></td>
<td><?php if(isset($_SESSION["today"]["total_topics"])) echo_html(format_number($_SESSION["today"]["total_topics"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("HitsCount") . " (" . text("Browsers") . ")"); ?></td>
<td><?php if(isset($_SESSION["yesterday"]["total_hits"])) echo_html(format_number($_SESSION["yesterday"]["total_hits"])); ?></td>
<td><?php if(isset($_SESSION["today"]["total_hits"])) echo_html(format_number($_SESSION["today"]["total_hits"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("HitsCount") . " (" . text("Bots") . ")"); ?></td>
<td><?php if(isset($_SESSION["yesterday"]["total_bot_hits"])) echo_html(format_number($_SESSION["yesterday"]["total_bot_hits"])); ?></td>
<td><?php if(isset($_SESSION["today"]["total_bot_hits"])) echo_html(format_number($_SESSION["today"]["total_bot_hits"])); ?></td>
</tr>

<?php if(!empty($settings["rates_active"])): ?>
<tr>
<td><?php echo_html(text("Rates")); ?></td>
<td>
<a href="search.php?do_search=1&rate_statistics=top_likes<?php echo($forum_appendix); ?>&statistics_period=previous_24" class="carma_plus" ><?php echo_html(format_number($_SESSION["yesterday"]["total_likes"])); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search.php?do_search=1&rate_statistics=top_dislikes<?php echo($forum_appendix); ?>&statistics_period=previous_24" class="carma_minus" ><?php echo_html(format_number($_SESSION["yesterday"]["total_dislikes"])); ?></a>

/ <a href="search.php?do_search=1&rate_statistics=top_rates<?php echo($forum_appendix); ?>&statistics_period=previous_24" class="carma_both" ><?php echo_html(format_number($_SESSION["yesterday"]["total_rates"])); ?></a>
<?php endif; ?>
</td>
<td>
<a href="search.php?do_search=1&rate_statistics=top_likes<?php echo($forum_appendix); ?>&statistics_period=last_24" class="carma_plus" ><?php echo_html(format_number($_SESSION["today"]["total_likes"])); ?></a>
<?php if(!empty($settings["dislikes_active"])): ?>
/ <a href="search.php?do_search=1&rate_statistics=top_dislikes<?php echo($forum_appendix); ?>&statistics_period=last_24" class="carma_minus" ><?php echo_html(format_number($_SESSION["today"]["total_dislikes"])); ?></a>

/ <a href="search.php?do_search=1&rate_statistics=top_rates<?php echo($forum_appendix); ?>&statistics_period=last_24" class="carma_both" ><?php echo_html(format_number($_SESSION["today"]["total_rates"])); ?></a>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>

</table>

<h3 class="profile_caption"><?php echo_html(text("PeriodStatistics")); ?></h3>

<!-- BEGIN: header2 -->

<div class="header2">

<form id="statistics_filter_form" action="statistics.php" method="post">
<input type="hidden" name="apply_filter" value="1">

<select name="fid" id="forum_activity_forum" onchange="reload_statistics()">
<option value=""><?php echo_html(text("AllForums")); ?></option>
<?php foreach($forum_list as $fid => $fdata):
$selected = (val_or_empty($_SESSION["forum_activity_forum"]) == $fid) ? "selected" : "";
?>
<option value="<?php echo_html($fid); ?>" <?php echo($selected); ?>><?php echo_html($fdata["name"]); ?></option>
<?php endforeach; ?>
</select>

<select name="period" id="forum_activity_period" class="forum_activity_period_select"  onchange="reload_statistics()">
  <?php $selected = reqvar("period") == "last_month" ? "selected" : ""; ?>
<option value="last_month" <?php echo($selected); ?>><?php echo_html(text("LastMonth")); ?></option>
  <?php $selected = (reqvar("period") == "last_half_year" || reqvar_empty("period")) ? "selected" : ""; ?>
<option value="last_half_year" <?php echo($selected); ?>><?php echo_html(text("LastHalfYear")); ?></option>
  <?php $selected = reqvar("period") == "last_year" ? "selected" : ""; ?>
<option value="last_year" <?php echo($selected); ?>><?php echo_html(text("LastYear")); ?></option>
  <?php $selected = reqvar("period") == "whole_period" ? "selected" : ""; ?>
<option value="whole_period" <?php echo($selected); ?>><?php echo_html(text("WholePeriod")); ?></option>
</select>

</form>

</div>

<!-- END: header2 -->

<table class="general_statistics_table">
<tr>
<th><?php echo_html(text("Index")); ?></th>
<th><?php echo_html(text("Value")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("NewUsers")); ?></td>
<td><?php if(isset($_SESSION["period"]["new_users"])) echo_html(format_number($_SESSION["period"]["new_users"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("OnlineMembers")); ?></td>
<td><?php if(isset($_SESSION["period"]["online_users"])) echo_html(format_number($_SESSION["period"]["online_users"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("MessagesCount")); ?></td>
<td><?php if(isset($_SESSION["period"]["total_posts"])) echo_html(format_number($_SESSION["period"]["total_posts"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("TopicsCount")); ?></td>
<td><?php if(isset($_SESSION["period"]["total_topics"])) echo_html(format_number($_SESSION["period"]["total_topics"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("HitsCount") . " (" . text("Browsers") . ")"); ?></td>
<td><?php if(isset($_SESSION["period"]["total_hits"])) echo_html(format_number($_SESSION["period"]["total_hits"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("HitsCount") . " (" . text("Bots") . ")"); ?></td>
<td><?php if(isset($_SESSION["period"]["total_bot_hits"])) echo_html(format_number($_SESSION["period"]["total_bot_hits"])); ?></td>
</tr>

<tr>
<td><?php echo_html(text("MessagesPerDay")); ?></td>
<td><?php if(isset($_SESSION["period"]["posts_per_day"])) echo_html(format_number($_SESSION["period"]["posts_per_day"])); ?></td>
</tr>

<?php if(!empty($settings["rates_active"])): ?>
<tr>
<td><?php echo_html(text("Rates")); ?></td>
<td>

<div class="total_rates_area">
<div class="load_total_rates" onclick="load_total_rates(this)"><?php echo_html(text("Show")); ?></div>
</div>

</td>
</tr>
<?php endif; ?>

</table>

<h3 class="profile_caption"><?php echo_html(text("DailyActivity")); ?></h3>

<div class="forum_activity_image_wrapper">
<img class="forum_activity_image" title="<?php echo_text("DailyActivity"); ?>" alt="<?php echo_text("DailyActivity"); ?>" src="ajax/forum_daily_activity_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<h3 class="profile_caption"><?php echo_html(text("MonthlyActivity")); ?></h3>

<div class="forum_activity_image_wrapper">
<img class="forum_activity_image" title="<?php echo_text("MonthlyActivity"); ?>" alt="<?php echo_text("MonthlyActivity"); ?>" src="ajax/forum_monthly_activity_diagram.php<?php echo($query_string); ?>&rnd=<?php echo(rand(1000, 9000)); ?>" onload="this.style.opacity = '1';">
</div>

<div id="browser_statistics">

<h3 class="profile_caption"><?php echo_html(text("Browsers")); ?> / <?php echo_html(text("OperatingSystems")); ?> / <?php echo_html(text("Bots")); ?></h3>

<div class="browser_stat_wrapper">
<input type="button" class="standard_button load_user_rates" value="<?php echo_html(text("Show")); ?>" onclick="load_browser_stats(this)">
</div>

</div>


<?php if (defined("GETGEOAPI_API_KEYS") && !empty(GETGEOAPI_API_KEYS) && empty($settings["hash_ip_addresses"])): ?>

<div id="geo_statistics">

<h3 class="profile_caption"><?php echo_html(text("GeoStatistics")); ?></h3>

<div class="browser_stat_wrapper">
<input type="button" class="standard_button load_user_rates" value="<?php echo_html(text("Show")); ?>" onclick="load_geo_stats(this)">
</div>

</div>

<?php endif; ?>

</div>

<!-- BEGIN: forum_bar -->

<div class="forum_bar">
<a href="forums.php"><?php echo_html(text("Forums")); ?></a>

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span>

<?php if(!$fmanager->is_logged_in() && !empty($_SESSION["ip_blocked"])): ?>
<span class="closed">[<?php echo_html(empty($_SESSION["ip_block_time_left"]) ? text("ip_blocked") : sprintf(text("ip_blocked_until"), $_SESSION["ip_block_time_left"])); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["activated"])): ?>
<span class="closed">[<?php echo_html(text("notActivated")); ?>]</span>
<?php elseif($fmanager->is_logged_in() && empty($_SESSION["approved"])): ?>
<span class="closed">[<?php echo_html(text("notApproved")); ?>]</span>
<?php elseif(!empty($_SESSION["blocked"])): 
$self_blocked_class = "";
if(val_or_empty($_SESSION["self_blocked"]) == 1) $self_blocked_class = "self_blocked";
elseif(val_or_empty($_SESSION["self_blocked"]) == 2) $self_blocked_class = "author_dead";
?>
<span class="closed <?php echo($self_blocked_class); ?>">[<?php echo_html(empty($_SESSION["block_time_left"]) ? text("blocked") : sprintf(text("blocked_until"), $_SESSION["block_time_left"])); ?>]</span>
<?php endif; ?>

/ <a href="load_statistics.php"><?php echo_html(text("LoadStatistics")); ?></a>

/ <span class="topic_title_main"><?php echo_html(text("Statistics")); ?></span> 
</div>

<!-- END: forum_bar -->
