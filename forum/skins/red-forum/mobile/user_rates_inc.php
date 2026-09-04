<?php if(!empty($settings["rates_active"])): ?>

<h3 class="profile_caption"><?php echo_html(text("LikeStatistics")); ?></h2>

<table class="like_table">
<tr>
<th colspan="2" style="width:50%"><a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_likes&author=<?php echo(reqvar("author")); ?>"><?php echo_html(text("MemberHasLiked")); ?> (<?php echo($total_likes); ?>)</a></th>
<th colspan="2" style="width:50%"><a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=<?php echo(reqvar("author")); ?>"><?php echo_html(text("MemberWasLiked")); ?> (<?php echo($total_liked); ?>)</a></th>
</tr>

<?php
$rowcount = max(count($likes), count($liked));
$row_class = "";

if($rowcount == 0)
{
?>
<tr>
<td>
&nbsp;
</td>
<td>
&nbsp;
</td>
<td>
&nbsp;
</td>
<td>
&nbsp;
</td>
</tr>
<?php
}
else
{
for($i = 0; $i < $rowcount; $i++)
{
?>

<tr class="<?php echo($row_class); ?>">
<td>
<div class="overflow_div narrow_column">
<?php if(empty($likes[$i])): ?>
&nbsp;
<?php
else:
?>

  <?php if(empty($likes[$i]["user_id"])): ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && empty($likes[$i]["user_ignored"]) && !empty($online_users["g_" . $likes[$i]["user"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <span class="<?php if(!empty($likes[$i]["user_ignored"])) echo("not_preferred"); ?>"><?php echo_html($likes[$i]["user"]); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($likes[$i]["user_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($likes[$i]["user_id"]); ?>" ><?php echo_html($likes[$i]["user"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
<?php endif; ?>

</div>
</td>
<td>
<?php
if(empty($likes[$i])) echo "&nbsp;";
else echo('<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=' . xrawurlencode(val_or_empty($likes[$i]["user_name"])) . '&rated_by=' . reqvar("author") . '">' . escape_html(format_number(val_or_empty($likes[$i]["cnt"]), 0)) . '</a>');
?>
</td>


<td>
<div class="overflow_div narrow_column">
<?php if(empty($liked[$i])): ?>
&nbsp;
<?php
else:
?>

  <?php if(empty($liked[$i]["user_id"])): ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && empty($liked[$i]["user_ignored"]) && !empty($online_users["g_" . $liked[$i]["user"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <span class="<?php if(!empty($liked[$i]["user_ignored"])) echo("not_preferred"); ?>"><?php echo_html($liked[$i]["user"]); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($liked[$i]["user_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($liked[$i]["user_id"]); ?>" ><?php echo_html($liked[$i]["user"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
<?php endif; ?>
</div>
</td>
<td>
<?php
if(empty($liked[$i])) echo "&nbsp;";
else echo('<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_liked&author=' . reqvar("author") . '&rated_by=' . xrawurlencode(val_or_empty($liked[$i]["user_name"])) . '">' . escape_html(format_number(val_or_empty($liked[$i]["cnt"]), 0)) . '</a>');
?>
</td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
<td></td>
<td></td>
</tr>
<?php endif; ?>

<?php
} // for
} // if
?>

</table>

<?php if(!empty($settings["dislikes_active"]) && empty($settings["dislikes_anonym"])): ?>

<h3 class="profile_caption"><?php echo_html(text("DislikeStatistics")); ?></h2>

<table class="like_table">
<tr>
<th colspan="2" style="width:50%"><a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_dislikes&author=<?php echo(reqvar("author")); ?>"><?php echo_html(text("MemberHasDisliked")); ?> (<?php echo($total_dislikes); ?>)</a></th>
<th colspan="2" style="width:50%"><a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=<?php echo(reqvar("author")); ?>"><?php echo_html(text("MemberWasDisliked")); ?> (<?php echo($total_disliked); ?>)</a></th>
</tr>

<?php
$rowcount = max(count($dislikes), count($disliked));
$row_class = "";

if($rowcount == 0)
{
?>
<tr>
<td>
&nbsp;
</td>
<td>
&nbsp;
</td>
<td>
&nbsp;
</td>
<td>
&nbsp;
</td>
</tr>
<?php
}
else
{
for($i = 0; $i < $rowcount; $i++)
{
?>

<tr class="<?php echo($row_class); ?>">
<td>
<div class="overflow_div narrow_column">
<?php if(empty($dislikes[$i])): ?>
&nbsp;
<?php
else:
?>

  <?php if(empty($dislikes[$i]["user_id"])): ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && empty($dislikes[$i]["user_ignored"]) && !empty($online_users["g_" . $dislikes[$i]["user"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <span class="<?php if(!empty($dislikes[$i]["user_ignored"])) echo("not_preferred"); ?>"><?php echo_html($dislikes[$i]["user"]); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($dislikes[$i]["user_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($dislikes[$i]["user_id"]); ?>" ><?php echo_html($dislikes[$i]["user"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
<?php endif; ?>
</div>
</td>
<td>
<?php
if(empty($dislikes[$i])) echo "&nbsp;";
else echo('<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=' . xrawurlencode(val_or_empty($dislikes[$i]["user_name"])) . '&rated_by=' . reqvar("author") . '">' . escape_html(format_number(val_or_empty($dislikes[$i]["cnt"]), 0)) . '</a>');
?>
</td>
<td>
<div class="overflow_div narrow_column">
<?php if(empty($disliked[$i])): ?>
&nbsp;
<?php
else:
?>

  <?php if(empty($disliked[$i]["user_id"])): ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && empty($disliked[$i]["user_ignored"]) && !empty($online_users["g_" . $disliked[$i]["user"]]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <span class="<?php if(!empty($disliked[$i]["user_ignored"])) echo("not_preferred"); ?>"><?php echo_html($disliked[$i]["user"]); ?><?php echo($online_status); ?></span>
  <?php else: ?>

  <?php
  $online_status = "";
  if(empty($settings["hide_online_status"]) && !empty($disliked[$i]["user_online"]))
  {
    $online_status = "&nbsp;<span class='online_text'>✓</span>";
  }
  ?>
  <a href="view_profile.php?uid=<?php echo_html($disliked[$i]["user_id"]); ?>" ><?php echo_html($disliked[$i]["user"]); ?></a><?php echo($online_status); ?>
  <?php endif; ?>
<?php endif; ?>
</div>
</td>
<td>
<?php
if(empty($disliked[$i])) echo "&nbsp;";
else echo('<a href="search_topic.php?do_search=1&post_sort=desc&author_mode=author_disliked&author=' . reqvar("author") . '&rated_by=' . xrawurlencode(val_or_empty($disliked[$i]["user_name"])) . '">' . escape_html(format_number(val_or_empty($disliked[$i]["cnt"]), 0)) . '</a>');
?>
</td>
</tr>

<?php if($rowcount > 25 && $i == 20): 
$row_class = "statistics_row_hidden";
?>
<tr>
<td><div class="statistics_list_expander" onclick="expand_statistics_list(this)">...</div></td>
<td></td>
<td></td>
<td></td>
</tr>
<?php endif; ?>

<?php
} // for
} // if
?>

</table>

<?php endif; // dislikes ?>

<?php endif; // likes ?>
