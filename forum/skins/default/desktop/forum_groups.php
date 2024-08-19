
<script>
function confirm_back()
{
  var form = document.getElementById("main_form");
  if(!form) return;

  if(!Forum.formDirty(form))
  {
    delay_redirect('<?php echo_html($target_url); ?>');
    return;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();

        delay_redirect('<?php echo_html($target_url); ?>');
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgCancelConfirm")); ?>", 'icon-warning.gif', mbuttons);

  return false;
}

var save_data_ajax = null;

function save_data()
{
  var form = document.getElementById('main_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!save_data_ajax)
  {
    save_data_ajax = new Forum.AJAX();

    save_data_ajax.timeout = TIMEOUT;

    save_data_ajax.beforestart = function() { break_check_new_messages(); };
    save_data_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    save_data_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          delay_redirect('forum_groups.php');
          return;
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    save_data_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  save_data_ajax.abort();
  save_data_ajax.resetParams();

  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('save_forum_groups', "1");

  save_data_ajax.setFormData(formData);

  save_data_ajax.request("ajax/process.php");

  return false;
} // save_data

var row_counter = -1;
var master_row = null;

function delete_row(button)
{
  var row = button.parentNode.parentNode;
  
  var tbody = row.parentNode;
  tbody.removeChild(row);
  
  if(tbody.children.length < 3) {
    add_row();
  }
}

function add_row()
{
  var table = document.getElementById("forum_goup_table");
  if(!table) return;
  
  var tbody = table.getElementsByTagName('tbody')[0];

  var last_row = tbody.lastElementChild;
  
  var new_row = master_row.cloneNode(true);
  
  var inputs = new_row.getElementsByTagName('input');
  
  var index = --row_counter;
  
  inputs[0].id = "group_" + index + "_name";
  inputs[0].name = "groups[" + index + "][name]";
  inputs[0].value = "";
  
  var sort = 0;
  for (var i = 1; i < tbody.children.length - 1; i++)
  {
     var row_inputs = tbody.children[i].getElementsByTagName('input');
     sort = Math.max(sort, row_inputs[1].value);
  }
  
  inputs[1].id = "group_" + index + "_sort_order";
  inputs[1].name = "groups[" + index + "][sort_order]";
  inputs[1].value = sort + 1;
  
  last_row.parentNode.insertBefore(new_row, last_row);
  
  inputs[0].focus();
}

Forum.addXEvent(window, 'load', function () { 
  var table = document.getElementById("forum_goup_table");
  if(!table) return;
  
  master_row = table.getElementsByTagName('tbody')[0].rows[1].cloneNode(true);

  var inputs = table.getElementsByTagName('tbody')[0].rows[1].getElementsByTagName('input');
  inputs[0].focus();
});
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

/ <span class="topic_title_main"><?php echo_html(text("ForumGroups")); ?></span>
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

<form action="settings.php" id="main_form" method="post" onsubmit="return save_data();">

<table id="forum_goup_table" class="form_table forum_group_table">

<tr>
<th><?php echo_html(text("Name")); ?></th>
<th><?php echo_html(text("Sort")); ?></th>
<th>&nbsp;</th>
</tr>

<?php foreach($forum_groups as $id => $forum_group_data): ?>
<tr>
<td><input type="text" id="group_<?php echo_html($id); ?>_name" name="groups[<?php echo_html($id); ?>][name]" value="<?php echo_html($forum_group_data["name"]); ?>"></td>
<td><input type="text" id="group_<?php echo_html($id); ?>_sort_order" name="groups[<?php echo_html($id); ?>][sort_order]" value="<?php echo_html($forum_group_data["sort_order"]); ?>"></td>
<td><input type="button" class="standard_button" value="" title="<?php echo_html(text("Delete")); ?>" onclick="delete_row(this)"></td>
</tr>
<?php endforeach; ?>

<tr>
<td colspan="3" class="button_area">
<div class="left_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Back")); ?>" onclick="confirm_back()">
</div>
<div class="right_buttons">
<input type="button" class="standard_button" value="<?php echo_html(text("Add")); ?>" onclick="add_row()">
<input type="submit" class="standard_button send_button" value="<?php echo_html(text("Save")); ?>">
</div>
<div class="clear_both">
</div>
</td>
</tr>

</table>

</form>

</div>