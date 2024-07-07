<script>
function expand_statistics_list(elm)
{
  var parent_table = elm.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  parent_table = parent_table.parentNode;
  if(!parent_table) return;
  
  var elms = parent_table.getElementsByClassName("statistics_row_hidden");
  for(var i = elms.length-1; i >= 0; i--)
  {
    elms[i].classList.remove("statistics_row_hidden");
  }
  
  elm = elm.parentNode;
  if(elm) elm = elm.parentNode;
  if(elm) elm = elm.style.display = "none";
}
</script>

<div class="content_area">

<!-- BEGIN: forum_bar -->

<div class="forum_bar">

<div class="forum_name_bar"><a href="forums.php"><?php echo_html(text("Forums")); ?></a> 

<?php
$display = "style='display:none'";
if(!empty($topics_with_new_count)) $display = "";
?>
<span class="new topics_with_new_indicator" <?php echo($display); ?>>[<a rel="nofollow" href="new_messages.php"><?php echo_html(text("new")); ?>:<span class='topics_with_new_count'><?php echo($topics_with_new_count); ?></span></a>]</span> 

/ <span class="topic_title_main"><?php echo_html(text("AnonymActivity")); ?></span>
</div>

<div class="forum_action_bar">
<table>
<tr>
<td>
<?php
$forum_selector_id = 1;
@include "forum_selector_inc.php";
?>
</td>
</tr>
</table>
</div>

<div class="clear_both">
</div>

</div>

<!-- END: forum_bar -->

<table class="form_table profile_table" style="margin-bottom: 0px">

<tr>
<th colspan="2"><?php echo_html(text("AnonymActivity")); ?></th>
</tr>

<tr>
<td><?php echo_html(text("Avatar")); ?>:</td>
<td>

<?php
$rnd = rand(1000, 9000);
$picture = $view_path . "images/guests.jpg";
?>

<img class="avatar_picture" src="<?php echo($picture); ?>" alt="<?php echo_html(text("Avatar")); ?>">

</td>
</tr>

<tr>
<td colspan="2"></td>
</tr>

<?php if(empty($settings["hide_online_status"])): ?>
<tr>
<td><?php echo_html(text("LastActivity")); ?>:</td>
<td><span class="number"><?php echo_html($guest_data["last_visit_date"]); ?></span></td>
</tr>

<tr>
<td colspan="2"></td>
</tr>
<?php endif; ?>

</table>

<?php 
$rowcount = count($read_topics);
if($rowcount > 0 && empty($settings["hide_online_status"])): 
$i = 0;
$row_class = "";
?>

<h3 class="profile_caption"><?php echo_html(text("ReadTopics")); ?></h2>

<table class="topic_statistic_table">

<tr>
<th><?php echo_html(text("Topic")); ?></th>
<th><?php echo_html(text("Forum")); ?></th>
<th><?php echo_html(text("DateTime")); ?></th>
</tr>

<?php foreach($read_topics as $tinfo):
$not_preferred = "";
if(!empty($_SESSION["preferred_forums"]) && empty($_SESSION["preferred_forums"][$tinfo["fid"]])) $not_preferred = "not_preferred";
?>

<tr class="<?php echo($row_class); ?>">
<td><div class="smart_break"><a href="topic.php?fid=<?php echo_html($tinfo["fid"]); ?>&tid=<?php echo_html($tinfo["tid"]); ?>&gotonew=1" rel="nofollow"><?php echo_html(postprocess_message($tinfo["name"])); ?></a></div></td>
<td><a href="forum.php?fid=<?php echo_html($tinfo["fid"]); ?>" class="<?php echo($not_preferred); ?>"><?php echo_html($tinfo["forum_name"]); ?></a></td>
<td class="date_col"><?php echo_html($tinfo["dt"]); ?></td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
<td></td>
</tr>
<?php endif; ?>

<?php 
$i++;
endforeach; 
?>

</table>

<?php endif; ?>



<div style="margin-bottom: 70px"></div>

</div>
