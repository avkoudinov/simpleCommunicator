<?php if(empty($settings["hide_online_status"]) && empty($_SESSION["skin_properties"][$skin]["no_online_users"])): ?>

<?php 
$add_class = pathinfo($_SERVER["PHP_SELF"], PATHINFO_FILENAME);

$ucnt = count($online_users);
if(!empty($online_users["g_#anonyms#"]["count"])) $ucnt += ($online_users["g_#anonyms#"]["count"] - 1);

$bcnt = 0;
foreach($online_users as $ouid => $uinfo)
{
  if(!empty($uinfo["bot"])) $bcnt++;
}

if (!empty($bcnt)) $ucnt = ($ucnt - $bcnt);

if (!empty($bcnt)) $ucnt .= "/" . $bcnt;
?>
<div class="header2 <?php echo($add_class); ?> all_online_users">
<?php echo_html(text("OnlineMembers")); ?> (<?php echo_html($ucnt); ?>):

<?php if($ucnt > 0)
{
  $users_str = "";
  foreach($online_users as $ouid => $uinfo)
  {
    $appendix = "";
    if($uinfo["time_ago"] != text("Now"))
      $appendix = "<span class='last_visit_info'>&nbsp;" . escape_html($uinfo["time_ago"]) . "</span>";

    if(!empty($uinfo["id"]))
      $users_str .= '<span class="user_name"><a href="view_profile.php?uid=' . escape_html($uinfo["id"]) . '">' . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif(!empty($uinfo["bot"]))
      $users_str .= "<span class='user_name'><a class='bot_link' href='view_bot_profile.php?bot=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif($ouid == "g_#anonyms#")
      $users_str .= "<span class='user_name'><i><a class='guest_link' href='view_anonym_activity.php'>" . escape_html($uinfo["name"]) . "</a></i>$appendix</span>, ";
    elseif($uinfo["name"] == "admin")
      $users_str .= "<span class='user_name'><a class='admin_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html(text("MasterAdministrator")) . "</a>$appendix</span>, ";
    else
      $users_str .= "<span class='user_name'><a class='guest_link' href='view_guest_profile.php?guest=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
  }

  echo trim($users_str, ", ");
}
?>
</div>

<?php endif; ?>