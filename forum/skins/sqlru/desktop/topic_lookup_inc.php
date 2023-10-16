<script>
function search_topic_on_enter(event)
{
  if(event.keyCode != 13) return true;

  search_topic();

  return false;
}

var search_topic_ajax = null;

function search_topic()
{
  var form = document.getElementById('topic_search_form');
  if(!form) return false;

  if(form.elements['topic_to_search'].value == '') 
  {
    form.elements['topic_to_search'].focus();
    return false;
  }

  var search_topic_button = document.getElementById('search_topic_button');
  if(search_topic_button) search_topic_button.classList.add("member_search_button_active");

  Forum.unselectAll(form.elements['found_topics']);

  if(!search_topic_ajax)
  {
    search_topic_ajax = new Forum.AJAX();

    search_topic_ajax.timeout = TIMEOUT;

    search_topic_ajax.beforestart = function() { break_check_new_messages(); };
    search_topic_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    search_topic_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(!response.success) 
        {
          if(search_topic_button) search_topic_button.classList.remove("member_search_button_active");
          return;
        }

        // remove old entries

        var found_topics = form.elements['found_topics'];

        for(var i = found_topics.length - 1; i >= 0 ; i--)
        {
          found_topics.options[i] = null;
        }

        if(response.found_topics && !Forum.isEmptyObject(response.found_topics))
        {
          for(var t in response.found_topics)
          {
            var option = new Option(response.found_topics[t],
                                    t.substring(1),
                                    true, false
                                   );
            found_topics.options[found_topics.options.length] = option;
          }
        }
        
        Forum.fireEvent(found_topics, 'change');
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
      
      if(search_topic_button) search_topic_button.classList.remove("member_search_button_active");
    };

    search_topic_ajax.onerror = function(error, url, info)
    {
      if(search_topic_button) search_topic_button.classList.remove("member_search_button_active");

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  search_topic_ajax.abort();
  search_topic_ajax.resetParams();

  search_topic_ajax.setPOST('search_moderated_topics', "1");
  search_topic_ajax.setPOST('hash', get_protection_hash());
  search_topic_ajax.setPOST('user_logged', user_logged);
  search_topic_ajax.setPOST('trace_sql', trace_sql);
  search_topic_ajax.setPOST('forum', "<?php echo_html(reqvar("fid")); ?>");
  search_topic_ajax.setPOST('merge_modus', form.elements['merge_modus'].value);
  search_topic_ajax.setPOST('topic_to_search', form.elements['topic_to_search'].value);

  search_topic_ajax.request("ajax/process.php");

  return false;
} // search_topic
</script>

<div id="search_topic_area" class="search_topic_area">

<form action="forum.php?fid=<?php echo_html(reqvar("fid")); ?>&fpage=<?php echo_html(reqvar("fpage")); ?>" id="topic_search_form" method="post" onsubmit="return search_topic();">
   <input type="hidden" name="merge_modus" value="">

   <table>
   <tr>
   <td style="width: 1%; white-space: nowrap">
   <?php echo_html(text("TargetTopic")); ?>:
   </td>
   <td ><input type="text" id="topic_to_search" name="topic_to_search" value="" placeholder="<?php echo_html(text("EnterTopicKeyword")); ?>" autocomplete="off" onkeypress="return search_topic_on_enter(event)"></td>
   <td style="text-align: right; width: 1%; padding-left: 0px">
   <input type="button" id="search_topic_button" class="standard_button member_search_button" value="<?php echo_html(text("Search")); ?>" onclick="search_topic()">
   </td>
   </tr>
   </table>

   <table>
   <tr>
   <td style="padding-top:0px">
   <select size="5" id="found_topics" name="found_topics" onDblClick="topic_choose_apply_func(topic_choose_apply_func.action)">
   </select>
   </td>
   </tr>
   </table>

   <table id="new_topic_block">
   <tr>
   <td style="padding-top:0px;">
   <?php echo_html(text("CreateNewTopic")); ?>:
   </td>
   </tr>
   <tr>
   <td style="padding-top:0px;">
   <input type="text" id="new_topic" name="new_topic" value="" placeholder="<?php echo_html(text("EnterNewTopicName")); ?>">
   </td>
   </tr>
   </table>
   
</form>   

</div>

