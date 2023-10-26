<?php if(empty($settings["hide_online_status"]) && empty($_SESSION["skin_properties"][$skin]["no_online_users"])): ?>

<?php 
$ucnt = count($online_users);
if(!empty($online_users["g_#anonyms#"]["count"])) $ucnt += ($online_users["g_#anonyms#"]["count"] - 1);
?>
<div class="header3 all_online_users">
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
      $users_str .= '<span class="user_name"><a href="view_profile.php?uid=' . escape_html($uinfo["id"]) . '" >' . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif(!empty($uinfo["bot"]))
      $users_str .= "<span class='user_name'><a class='bot_link' href='view_bot_profile.php?bot=" . xrawurlencode($uinfo["name"]) . "'>" . escape_html($uinfo["name"]) . "</a>$appendix</span>, ";
    elseif($ouid == "g_#anonyms#")
      $users_str .= "<span class='user_name'><i>" . escape_html($uinfo["name"]) . "</i>$appendix</span>, ";
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
