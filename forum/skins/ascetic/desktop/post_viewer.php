<style>
.post_checkbox
{
  display: none;
}
</style>

<script src='<?php echo($view_path); ?>topic.js'></script>

<script>

var user_tags = {};

<?php foreach($user_tags as $tgid => $tgname): ?> 
user_tags['#<?php echo_js($tgid); ?>'] = '<?php echo_js($tgname); ?>';
<?php endforeach; ?> 

function update_post_footer(msg) 
{
  var elms = document.getElementsByClassName("post_footer");
  if(elms.length < 2) return;
  
  while (elms[1].firstChild) {
      elms[1].removeChild(elms[1].firstChild);
  }
  
  var elm = document.createElement('div');
  elm.classList.add('user_post_actions');
  elm.innerHTML = "<a href='topic.php?tid=0&msg=" + msg + "' target='_blank'>" + "<?php echo_js(text("GotoTopic")); ?>" + "</a>";

  elms[1].appendChild(elm);
}

function convert_action_link(target, params)
{
  var elm;

  if(target == "add_post_to_favourites")
  {
    elm = document.getElementById("favourite_post_link_" + params.post);
    if(elm)
    {
      elm.title = "<?php echo_js(text("AddToFavourites")); ?>";
      elm.classList.remove('post_in_favourites');
      elm.classList.add('post_not_in_favourites');
      elm.onclick = function (event) { return do_action({ topic_action: "add_post_to_favourites", post: params.post }); }
    }
  }

  if(target == "remove_post_from_favourites")
  {
    elm = document.getElementById("favourite_post_link_" + params.post);
    if(elm)
    {
      elm.title = "<?php echo_js(text("RemoveFromFavourites")); ?>";
      elm.classList.remove('post_not_in_favourites');
      elm.classList.add('post_in_favourites');
      elm.onclick = function (event) { return do_action({ topic_action: "remove_post_from_favourites", post: params.post }); }
    }
  }
}

var reload_post_ajax = null;

function load_post_by_id()
{
  var elm = document.getElementById("post_id");
  if(!elm) return false;
  
  var params = { post: elm.value };

  hide_all_popups();

  Forum.show_sys_progress_indicator(true);

  if(!reload_post_ajax)
  {
    reload_post_ajax = new Forum.AJAX();

    reload_post_ajax.timeout = TIMEOUT;

    reload_post_ajax.beforestart = function() { break_check_new_messages(); };
    reload_post_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    reload_post_ajax.onload = function(text, xml)
    {
      try
      {
        var post_node = document.getElementById("post_node");

        if(post_node)
        {
          // remove old possible transfer file
          var elm = document.getElementById('ajax_data');
          if(elm) elm.parentNode.removeChild(elm);
          
          post_node.innerHTML = text;

          init_citations();
          init_more_buttons();
          init_lightbox_images();

          // highlichting code if not highlighted yet
          var codes = post_area.getElementsByTagName('code');
          for(var i = 0; i < codes.length; i++)
          {
            if(!codes[i].classList.contains("hljs")) hljs.highlightBlock(codes[i]);
          }

          elm = document.getElementById('post_head_' + this.post);
          if(elm) set_current_post(elm);

          elm = document.getElementById('ajax_data');
          if(elm) extract_and_handle_attributes(elm);
          
          // reload images
          var imgs = post_node.getElementsByClassName('post_image');
          for(var i = 0; i < imgs.length; i++)
          {
            imgs[i].src = imgs[i].src + (imgs[i].src.indexOf('?') != -1 ? '&' : '?') + "d=" + new Date().getTime();            
          }
          
          update_post_footer(this.post);
        }

        if(messages) Forum.handle_response_messages(messages);
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    reload_post_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  } // init ajax

  reload_post_ajax.abort();
  reload_post_ajax.resetParams();

  reload_post_ajax.post = elm.value;

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    reload_post_ajax.setPOST(p, params[p]);
  }

  reload_post_ajax.setPOST('hash', get_protection_hash());
  reload_post_ajax.setPOST('user_logged', user_logged);

  reload_post_ajax.request("ajax/load_post.php");

  return false;
}

</script>

<form onsubmit="return false;">
<table class="aux_table">
<td>
<input type="text" class="search_field" id="post_id" placeholder="Идентифкатор поста">
</td>
<td>
<input type="submit" class="standard_button search_button" value="Показать" onclick="load_post_by_id()">
</td>
</tr>
</tbody></table>
</form>

<div id="post_area">
  <div id="post_node"></div>
</div>