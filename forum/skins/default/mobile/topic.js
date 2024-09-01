var selected_posts = {};
var selected_tags = {};

var cancel_confirmed = false;

function expand_code(header)
{
  if(!header.classList.contains('code_header_expanded'))
  {
    header.classList.add('code_header_expanded');
  }

  if(!header.nextElementSibling.classList.contains('code_expanded'))
  {
    header.nextElementSibling.classList.add('code_expanded');
  }
  
  // expand possible child quotes
  var child_quotes = header.nextElementSibling.querySelectorAll('.quote');
  var cnt = child_quotes.length;
  for(var i = cnt-1; i >= 0; i--)
  {
    remove_expander(child_quotes[i]);
  }

  // expand the whole post when expanding the spoiler
  var _parent = header.parentNode;
  while(_parent)
  {
    if(_parent.classList.contains('quote'))
    {
      remove_expander(_parent);
    }

    if(_parent.classList.contains('message_text'))
    {
      _parent.style.maxHeight = 'none';

      _parent.nextElementSibling.style.display = 'none';
      break;
    }
    
    _parent = _parent.parentNode;
  }
}

function toggle_spoiler(header)
{
  if(header.classList.contains('spoiler_header_opened'))
  {
    header.classList.remove('spoiler_header_opened');
  }
  else
  {
    header.classList.add('spoiler_header_opened');
  }

  if(header.nextElementSibling.classList.contains('spoiler_opened'))
  {
    header.nextElementSibling.classList.remove('spoiler_opened');
  }
  else
  {
    header.nextElementSibling.classList.add('spoiler_opened');
  }

  // expand possible child quotes
  var child_quotes = header.nextElementSibling.querySelectorAll('.quote');
  var cnt = child_quotes.length;
  for(var i = cnt-1; i >= 0; i--)
  {
    remove_expander(child_quotes[i]);
  }

  // expand the whole post when expanding the spoiler
  var _parent = header.parentNode;
  while(_parent)
  {
    if(_parent.classList.contains('quote'))
    {
      remove_expander(_parent);
    }

    if(_parent.classList.contains('message_text'))
    {
      _parent.style.maxHeight = 'none';

      _parent.nextElementSibling.style.display = 'none';
      break;
    }
    
    _parent = _parent.parentNode;
  }
}

var px_ratio = window.devicePixelRatio || window.screen.availWidth / document.documentElement.clientWidth;
function expandOnZoom()
{
    var newPx_ratio = window.devicePixelRatio || window.screen.availWidth / document.documentElement.clientWidth;
    if(newPx_ratio != px_ratio)
    {
        // zooming
        if (newPx_ratio > px_ratio)
        {
          var elms = document.querySelectorAll('.quote');
          var cnt = elms.length;
          for(var i = cnt-1; i >= 0; i--)
          {
            remove_expander(elms[i]);
          }

          var elms = document.querySelectorAll('.message_text');
          var cnt = elms.length;
          for(var i = 0; i < cnt; i++)
          {
            elms[i].style.maxHeight = 'none';

            elms[i].nextElementSibling.style.display = 'none';
          }
        }
        
        px_ratio = newPx_ratio;
        return true;
    }else{
        // just resizing
        return false;
    }
}
Forum.addXEvent(window, 'resize', expandOnZoom);

function show_ignored_post(btn, pid)
{
  btn.style.display = 'none';
  
  var elm = document.getElementById("post_table_" + pid);
  if(elm) elm.classList.remove('ignored_post');
  
  init_more_buttons();
}

function show_smile_tab(pos)
{
  var elms = document.getElementsByClassName("smile_tab");
  if(pos >= elms.length) return;
  
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].classList.remove('active');
  }  

  elms[pos].classList.add('active');

  elms = document.getElementsByClassName("smile_tab_contents");

  for(var i = 0; i < elms.length; i++)
  {
    elms[i].classList.remove('active');
  }  

  elms[pos].classList.add('active');
}

function embed_youtube(elm, code, start)
{
  var parent = elm.parentNode;
  while(parent)
  {
    if(parent.classList && parent.classList.contains('youtube_container'))
    {
      // remove all previous children
      var fc = parent.firstChild;
      while(fc)
      {
        parent.removeChild(fc);
        fc = parent.firstChild;
      }

      // add iframe
      var iframe = document.createElement('iframe');
      iframe.style.width = parent.clientWidth + "px";
      iframe.style.height = parent.clientHeight + "px";
      iframe.style.border = "0";
      iframe.src = "https://www.youtube.com/embed/" + code + "?autoplay=1" + "&start=" + start + "&enablejsapi=1";
      iframe.setAttribute("allowfullscreen", "1");
      //iframe.addEventListener('load', () => iframe.contentWindow.postMessage('{ "event": "command", "func": "playVideo", "args": ""}', '*'), true);

      parent.appendChild(iframe);

      setTimeout(() => { iframe.contentWindow.postMessage('{ "event": "command", "func": "playVideo", "args": ""}', '*'); }, 1000);

      break;
    }

    parent = parent.parentNode;
  }
}

function embed_vimeo(elm, code, at)
{
  var parent = elm.parentNode;
  while(parent)
  {
    if(parent.classList && parent.classList.contains('vimeo_container'))
    {
      // remove all previous children
      var fc = parent.firstChild;
      while(fc)
      {
        parent.removeChild(fc);
        fc = parent.firstChild;
      }

      // add iframe
      var iframe = document.createElement('iframe');
      iframe.style.width = parent.clientWidth + "px";
      iframe.style.height = parent.clientHeight + "px";
      iframe.style.border = "0";
      iframe.src = "https://player.vimeo.com/video/" + code + "?autoplay=1#at=" + at;
      iframe.setAttribute("allowfullscreen", "1");
      iframe.setAttribute("webkitallowfullscreen", "1");
      iframe.setAttribute("mozallowfullscreen", "1");


      parent.appendChild(iframe);

      break;
    }

    parent = parent.parentNode;
  }
}

function embed_vkvideo(elm, player)
{
  var parent = elm.parentNode;
  while(parent)
  {
    if(parent.classList && parent.classList.contains('vkvideo_container'))
    {
      // remove all previous children
      var fc = parent.firstChild;
      while(fc)
      {
        parent.removeChild(fc);
        fc = parent.firstChild;
      }

      // add iframe
      var iframe = document.createElement('iframe');
      iframe.style.width = parent.clientWidth + "px";
      iframe.style.height = parent.clientHeight + "px";
      iframe.style.border = "0";
      iframe.src = player;

      parent.appendChild(iframe);

      break;
    }

    parent = parent.parentNode;
  }
}

function embed_rutube(elm, code, bmstart)
{
  var parent = elm.parentNode;
  while(parent)
  {
    if(parent.classList && parent.classList.contains('rutube_container'))
    {
      // remove all previous children
      var fc = parent.firstChild;
      while(fc)
      {
        parent.removeChild(fc);
        fc = parent.firstChild;
      }

      // add iframe
      var iframe = document.createElement('iframe');
      iframe.style.width = parent.clientWidth + "px";
      iframe.style.height = parent.clientHeight + "px";
      iframe.style.border = "0";
      iframe.src = "https://rutube.ru/play/embed/" + code + "?autoStart=1&bmstart=" + bmstart;
      iframe.setAttribute("allowfullscreen", "1");
      iframe.setAttribute("webkitallowfullscreen", "1");
      iframe.setAttribute("mozallowfullscreen", "1");


      parent.appendChild(iframe);

      break;
    }

    parent = parent.parentNode;
  }
}

function embed_coub(elm, code)
{
  var parent = elm.parentNode;
  while(parent)
  {
    if(parent.classList && parent.classList.contains('coub_container'))
    {
      // remove all previous children
      var fc = parent.firstChild;
      while(fc)
      {
        parent.removeChild(fc);
        fc = parent.firstChild;
      }

      // add iframe
      var iframe = document.createElement('iframe');
      iframe.style.width = parent.clientWidth + "px";
      iframe.style.height = parent.clientHeight + "px";
      iframe.style.border = "0";
      iframe.style.backgroundColor = 'black';
      iframe.src = "https://coub.com/embed/" + code + "?muted=false&autostart=true&originalSize=false&startWithHD=false";
      iframe.setAttribute("allowfullscreen", "true");

      parent.appendChild(iframe);

      break;
    }

    parent = parent.parentNode;
  }
}

function update_moderator_warning(pid, response)
{
  var elm = document.getElementById("modwarning_" + pid);
  if(elm) 
  {
     if(response.warning) elm.style.display = "block";
     else                 elm.style.display = "none";
  }

  elm = document.getElementById("modwarning_moderator_" + pid);
  if(elm) 
  {
    if(response.warned_by) elm.innerHTML = response.warned_by + ":";
    else                   elm.innerHTML = "";
  }

  elm = document.getElementById("modwarning_warning_" + pid);
  if(elm) 
  {
    if(response.warning) 
    {
      elm.innerHTML = response.warning;
      init_lightbox_images();
    }
    else                 
    {
      elm.innerHTML = "";
    }
  }
}

function show_hide_post_favourite_load(pid, state)
{
  var elm = document.getElementById("favourite_post_link_" + pid);
  if(!elm) return;

  if(state) elm.classList.add("post_favourites_loading");
  else      elm.classList.remove("post_favourites_loading");
}

function show_hide_post_subscribe_load(pid, state)
{
  var elm = document.getElementById("subscribe_post_link_" + pid);
  if(!elm) return;

  if(state) elm.classList.add("post_subscribe_loading");
  else      elm.classList.remove("post_subscribe_loading");
}

function show_hide_post_load(pid, state)
{
  var elm = document.getElementById("post_rating_loading_" + pid);

  if(state)
  {
    if(elm) elm.style.display = "table-cell";

    elm = document.getElementById("post_rating_up_" + pid);
    if(elm) elm.style.display = "none";

    elm = document.getElementById("post_rating_down_" + pid);
    if(elm) elm.style.display = "none";

    elm = document.getElementById("post_rating_del_" + pid);
    if(elm) elm.style.display = "none";
  }
  else
  {
    if(elm) elm.style.display = "none";
  }
}

function set_new_post_rating(pid, rating)
{
  var elm;

  elm = document.getElementById("post_rating_up_" + pid);
  if(elm) elm.style.display = (rating.reset) ? "table-cell" : "none";

  elm = document.getElementById("post_rating_down_" + pid);
  if(elm) elm.style.display = (rating.reset) ? "table-cell" : "none";

  elm = document.getElementById("post_rating_del_" + pid);
  if(elm) elm.style.display = (rating.reset) ? "none" : "table-cell";

  elm = document.getElementById("post_rating_plus_" + pid);
  if(elm) elm.innerHTML = rating.plus;

  elm = document.getElementById("post_rating_minus_" + pid);
  if(elm) elm.innerHTML = rating.minus;
}

function select_all()
{
  var elms = document.getElementsByClassName("post_checkbox");
  for(var i = 0; i < elms.length; i++)
  {
    pid = elms[i].getAttribute("data-pid");
    if(!pid) continue;
    
    if(!elms[i].parentNode.parentNode.classList.contains('selected_post_row'))
    {
      elms[i].parentNode.parentNode.classList.add('selected_post_row');
      selected_posts[pid] = 1;
    }
  }  
  
  var count = Forum.objectPropertiesCount(selected_posts);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_posts_count");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].innerHTML = count;
  }
  
  return false;
}

function unselect_all()
{
  var elms = document.getElementsByClassName("post_checkbox");
  for(var i = 0; i < elms.length; i++)
  {
    pid = elms[i].getAttribute("data-pid");
    if(!pid) continue;
    
    if(elms[i].parentNode.parentNode.classList.contains('selected_post_row'))
    {
      elms[i].parentNode.parentNode.classList.remove('selected_post_row');
      delete selected_posts[pid];
    }
  }  
  
  hide_all_popups();
  
  return false;
}

function toggle_selection(div, pid)
{
  if(!pid) return;
    
  if(div.parentNode.parentNode.classList.contains('selected_post_row'))
  {
    div.parentNode.parentNode.classList.remove('selected_post_row');
    delete selected_posts[pid];
  }
  else
  {
    div.parentNode.parentNode.classList.add('selected_post_row');
    selected_posts[pid] = 1;
  }
}

var current_displayed_user_notes = null;
var current_displayed_post_id_info = null;
var current_moderator_post_more_panel = null;
var liked_users_panel = null;
var disliked_users_panel = null;
var voted_users_panel = null;

function toggle_id_info_actions(pid)
{
  elm = document.getElementById("post_id_info_" + pid);
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
    if(document.getElementById("pid_link_" + pid)) focus_field("pid_link_" + pid);
    else if(document.getElementById("pid_lmsg_" + pid)) focus_field("pid_lmsg_" + pid);
    current_displayed_post_id_info = elm;
  }

  return false;
}

function toggle_user_notes(pid)
{
  elm = document.getElementById("user_notes_" + pid);
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
    current_displayed_user_notes = elm;
  }

  return false;
}

function toggle_voted_users(oid)
{
  elm = document.getElementById("voted_users_" + oid);
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
    voted_users_panel = elm;
  }

  return false;
}

function toggle_liked_users(pid)
{
  elm = document.getElementById("liked_users_" + pid);
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
    liked_users_panel = elm;
  }

  return false;
}

function toggle_disliked_users(pid)
{
  elm = document.getElementById("disliked_users_" + pid);
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
    disliked_users_panel = elm;
  }

  return false;
}

function toggle_filter_actions()
{
  elm = document.getElementById("filter_actions");
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
  }

  return false;
}

function toggle_moderator_post_more_actions(pid)
{
  elm = document.getElementById("moderator_post_more_" + pid);
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
    current_moderator_post_more_panel = elm;
  }

  return false;
}

function toggle_media_selection_area()
{
  var elm = document.getElementById("media_selection_area");
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
  }

  return false;
}

function toggle_code_selection_area()
{
  var elm = document.getElementById("code_selection_area");
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
  }

  return false;
}

function toggle_color_selection_area()
{
  var elm = document.getElementById("color_selection_area");
  if(!elm) return false;

  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    elm.style.display = "block";
  }

  return false;
}

function hide_smile_selection_area()
{
  var elm = document.getElementById("smile_selection_area");
  if(!elm) return false;
  
  elm.style.display = "none";
}

function toggle_smile_selection_area()
{
  var elm = document.getElementById("smile_selection_area");
  if(!elm) return false;

  hide_all_popups();

  if(elm.style.display == "none")
  {
    elm.style.display = "table-row";
  }
  else
  {
    elm.style.display = "none";
  }

  elm = document.getElementById("message");
  if(elm) elm.focus();

  setTimeout(function () {
    elm = document.getElementById("smile_tab_contents");
    if(elm) elm.scrollIntoView();
  }, 200);

  return false;
}

function toggle_forum_selection_area()
{
  var elm = document.getElementById("forum_selection_area");
  if(!elm) return false;
  
  var need_show = (elm.style.display == "none");

  hide_all_popups();

  if(need_show)
  {
    reset_forum_selector('forum_selector_move');

    elm.style.display = "block";

    elm = document.getElementById("forum_selector_move");
    if (elm) elm.focus();
  }

  return false;
}

function show_moderator_popup_menu(pid)
{
  hide_all_popups();

  var count = Forum.objectPropertiesCount(selected_posts);

  if(count == 0) return false;

  var elms = document.getElementsByClassName("selected_posts_count");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].innerHTML = count;
  }

  var elm = document.getElementById("moderator_popup_menu_" + pid);
  if(!elm) return false;

  elm.style.display = "block";

  return false;
}

function hide_all_popups()
{
  Forum.hide_sys_bubblebox();

  if(current_displayed_post_id_info)
  {
    current_displayed_post_id_info.style.display = "none";
    current_displayed_post_id_info = null;
  }

  if(current_displayed_user_notes)
  {
    current_displayed_user_notes.style.display = "none";
    current_displayed_user_notes = null;
  }

  if(current_moderator_post_more_panel)
  {
    current_moderator_post_more_panel.style.display = "none";
    current_moderator_post_more_panel = null;
  }

  if(voted_users_panel)
  {
    voted_users_panel.style.display = "none";
    voted_users_panel = null;
  }

  if(liked_users_panel)
  {
    liked_users_panel.style.display = "none";
    liked_users_panel = null;
  }

  if(disliked_users_panel)
  {
    disliked_users_panel.style.display = "none";
    disliked_users_panel = null;
  }

  var elm = document.getElementById("forum_selection_area");
  {
    if(elm) elm.style.display = "none";
    
    elm = document.getElementById("forum_selection_list");
    if(elm) Forum.unselectAll(elm);    
  }

  elm = document.getElementById("filter_actions");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("media_selection_area");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("code_selection_area");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("color_selection_area");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("citation_dialog");
  if(elm) elm.style.display = "none";

  var elms = document.getElementsByClassName("popup_moderator_menu");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].style.display = "none";
  }

  elms = document.getElementsByClassName("manage_tags_list");
  for(var i = 0; i < elms.length; i++)
  {
    hide_manage_tags_list(elms[i].getAttribute('data-pid'));
  }

  elms = document.getElementsByClassName("profiling_info");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].style.display = "none";
  }
}

function user_esc_handler()
{
  hide_all_popups();
  hide_appeal_authors_lookup();
}

function unset_topic_new_markers()
{
  signal_new(false);
  
  var elm = document.getElementById("new_messages_alertbox");
  if(elm) elm.style.display = "none";

  var news = document.getElementsByClassName("new_messages_indicator");
  for(var i = 0; i < news.length; i++)
  {
    news[i].style.display = "none";
  }
}

function set_topic_new_markers(new_messages_count)
{
  if(!new_messages_count || new_messages_count == 0) return;
  
  var news = document.getElementsByClassName("new_messages_count");
  for(var i = 0; i < news.length; i++)
  {
    news[i].innerHTML = new_messages_count;
  }

  news = document.getElementsByClassName("new_messages_indicator");
  for(var i = 0; i < news.length; i++)
  {
    news[i].style.display = "inline";
  }
}

async function reload_attachment(att, nr)
{
  await fetch("ajax/attachment.php?aid=" + att + "&nr=" + nr + "&picture=1", {cache: 'no-cache'});
  await fetch("ajax/attachment.php?aid=" + att + "&nr=" + nr + "&thumb=1&picture=1", {cache: 'no-cache'});
  await fetch("ajax/attachment.php?attachment_button=1&aid=" + att + "&nr=" + nr, {cache: 'no-cache'});
  await fetch("ajax/attachment.php?attachment_del_indicator=1&aid=" + att + "&nr=" + nr, {cache: 'no-cache'});

  var atts = document.getElementsByClassName('attachment_picture_' + att + '_' + nr);
  for(var i = 0; i < atts.length; i++)
  {
    atts[i].src = "ajax/attachment.php?aid=" + att + "&nr=" + nr + "&thumb=1&picture=1&doing_delete=" + new Date().getTime();
  }

  atts = document.getElementsByClassName('attachment_button_' + att + '_' + nr);
  for(var i = 0; i < atts.length; i++)
  {
    atts[i].style.backgroundImage = "url('ajax/attachment.php?attachment_button=1&aid=" + att + "&nr=" + nr + "&d=" + new Date().getTime() + "')";
  }

  atts = document.getElementsByClassName('attachment_del_indicator_' + att + '_' + nr);
  for(var i = 0; i < atts.length; i++)
  {
    atts[i].style.backgroundImage = "url('ajax/attachment.php?attachment_del_indicator=1&aid=" + att + "&nr=" + nr + "&d=" + new Date().getTime() + "')";
  }

  Forum.show_sys_progress_indicator(false);
}

function set_version_loading(pid, vid, state)
{
  var ver = document.getElementById("loading_version_" + pid);
  if(!ver) return;

  if(state) ver.style.display = 'inline';
  else      ver.style.display = 'none';
}

function set_version_loaded(pid, vid, version_content, version_list)
{
  var msg = document.getElementById("message_text_" + pid);
  if(msg)
  {
    msg.innerHTML = version_content;
  }
  
  msg = document.getElementById("update_info_" + pid);
  if(msg)
  {
    msg.innerHTML = ((vid == '') ? msg_Modified : msg_Version) + ': ' + Forum.selectedText(version_list);
  }

  init_more_buttons();
  init_lightbox_images();
}

function extract_attributes(elm)
{
  var result;
  var att;
  var news;
  var i, j;
  
  if(!elm.attributes) return false;
  
  for(i = 0; i < elm.attributes.length; i++) 
  {
    att = elm.attributes[i];
    if(!att) continue;
    
    if(att.name == "data-last_message") 
    {
      last_message = att.value;
      continue;      
    }
    
    if(att.name == "data-first_new_message") 
    {
      first_new_message = att.value;
      if (first_message == 0) first_message = first_new_message;
      continue;      
    }

    if(att.name == "data-loaded_new_posts_count") 
    {
      loaded_message_count += parseInt(att.value);
      continue;      
    }

    if(att.name == "data-force_redirect" && att.value == "1") 
    {
      return true;      
    }
    
    if(att.name == "data-remaining_new_posts_count") 
    {
      if(att.value != 0 && att.value != "")
      {
        news = document.getElementsByClassName("new_count");
        for(j = 0; j < news.length; j++)
        {
          news[j].innerHTML = att.value;
        }
 
        elm = document.getElementById("new_messages_alertbox");
        if(elm) elm.style.display = "block";

        news = document.getElementsByClassName("new_messages_indicator");
        for(j = 0; j < news.length; j++)
        {
          news[j].style.display = "inline";
        }
      }
      else
      {
        if(typeof unset_topic_new_markers == 'function') unset_topic_new_markers();
      }
      
      continue;      
    }
    
    if(result = att.name.match(/data-(.*)/i))
    {
      messages[result[1].toUpperCase()] = att.value;
      continue;      
    }
  }
  
  return false;
}

var reload_post_ajax = null;

function reload_post(post)
{
  var params = { post: post, in_search: in_search };

  hide_all_popups();

  Forum.show_sys_progress_indicator(true);

  if(!reload_post_ajax)
  {
    reload_post_ajax = new Forum.AJAX();

    reload_post_ajax.timeout = TIMEOUT;

    reload_post_ajax.beforestart = function() { break_check_new_messages(); };
    reload_post_ajax.aftercomplete = function(error) { 
      activate_check_new_messages(); 
      check_new_messages();
    };

    reload_post_ajax.onload = function(text, xml)
    {
      try
      {
        var post_node = document.getElementById("post_" + this.post);

        if(post_node)
        {
          // remove old possible transfer file
          var elm = document.getElementById('ajax_data');
          if(elm) elm.parentNode.removeChild(elm);
          
          post_node.innerHTML = text;
          
          setTimeout(function () {
            init_lightbox_images();
            init_embedded_widgets();

            // highlichting code if not highlighted yet
            var codes = post_area.getElementsByTagName('code');
            for(var i = 0; i < codes.length; i++)
            {
              if(!codes[i].classList.contains("hljs")) hljs.highlightBlock(codes[i]);
            }

            set_current_post(reload_post_ajax.post);

            elm = document.getElementById('ajax_data');
            if(elm) extract_attributes(elm);

            // reload images
            var imgs = post_node.getElementsByClassName('post_image');
            for(var i = 0; i < imgs.length; i++)
            {
              imgs[i].src = imgs[i].src + (imgs[i].src.indexOf('?') != -1 ? '&' : '?') + "d=" + new Date().getTime();            
            }

            // by using insertAdjacentHTML for a content with images
            // they are loaded not immediately, we need a timeout before
            // calcualtion of the heights
            setTimeout(init_more_buttons, 1000);
            setTimeout(init_more_buttons, 2500);

            exec_reload_nav_control('message_info_bar', first_new_message);
            exec_reload_nav_control('navigator_bar', first_new_message);
            exec_reload_online_users();
          }, 200);
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

  reload_post_ajax.post = post;

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    reload_post_ajax.setPOST(p, params[p]);
  }

  reload_post_ajax.setPOST('hash', get_protection_hash());
  reload_post_ajax.setPOST('user_logged', user_logged);
  reload_post_ajax.setPOST('trace_sql', trace_sql);

  reload_post_ajax.setPOST('fpage', fpage);

  reload_post_ajax.request("ajax/load_post.php");

  return false;
}

function reload_nav_control(params)
{
  var reload_nav_control_ajax = new Forum.AJAX();

  reload_nav_control_ajax.timeout = TIMEOUT;

  reload_nav_control_ajax.beforestart = function() { break_check_new_messages(); };
  reload_nav_control_ajax.aftercomplete = function(error) { 
    activate_check_new_messages(); 
    check_new_messages();
  };

  reload_nav_control_ajax.onload = function(text, xml)
  {
    if(text.trim() == '') return;

    try
    {
      var navs = document.getElementsByClassName(params.ctrl);
      for(var i = 0; i < navs.length; i++)
      {
        navs[i].innerHTML = text;
        
        var jscripts = navs[i].getElementsByTagName('script')
        for (var j = 0; j < jscripts.length; j++)
        {
          eval(jscripts[j].innerHTML);       
        }
      }
    }
    catch(err)
    {
    }
  };

  reload_nav_control_ajax.onerror = function(error, url, info)
  {
  };

  reload_nav_control_ajax.abort();
  reload_nav_control_ajax.resetParams();

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    reload_nav_control_ajax.setPOST(p, params[p]);
  }

  reload_nav_control_ajax.setPOST('hash', get_protection_hash());
  reload_nav_control_ajax.setPOST('user_logged', user_logged);
  reload_nav_control_ajax.setPOST('trace_sql', trace_sql);

  reload_nav_control_ajax.setPOST('fpage', fpage);

  reload_nav_control_ajax.request("ajax/reload_nav_control.php");

  return false;
}

var reload_online_users_ajax = null;

function reload_online_users(topic, forum)
{
  var params = { topic: topic, forum: forum };

  if(!reload_online_users_ajax)
  {
    reload_online_users_ajax = new Forum.AJAX();

    reload_online_users_ajax.timeout = TIMEOUT;

    reload_online_users_ajax.beforestart = function() { break_check_new_messages(); };
    reload_online_users_ajax.aftercomplete = function(error) { 
      activate_check_new_messages(); 
      check_new_messages();
    };

    reload_online_users_ajax.onload = function(text, xml)
    {
      if(text.trim() == '') return;

      try
      {
        var navs = document.getElementsByClassName('online_users_area');
        for(var i = 0; i < navs.length; i++)
        {
          navs[i].innerHTML = text;
        }
      }
      catch(err)
      {
      }
    };

    reload_online_users_ajax.onerror = function(error, url, info)
    {
    };
  } // init ajax

  reload_online_users_ajax.abort();
  reload_online_users_ajax.resetParams();

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    reload_online_users_ajax.setPOST(p, params[p]);
  }

  reload_online_users_ajax.setPOST('hash', get_protection_hash());
  reload_online_users_ajax.setPOST('user_logged', user_logged);
  reload_online_users_ajax.setPOST('trace_sql', trace_sql);

  reload_online_users_ajax.request("ajax/reload_online_users.php");

  return false;
}

function get_actual_last_message()
{
  var posts = document.getElementsByClassName("post_table");
  if (posts.length == 0) return last_message;
  
  return posts[posts.length - 1].getAttribute("data-pid");
}

var load_created_post_ajax = null;

function load_created_post(created_post, original_post, on_loaded)
{
  hide_all_popups();

  Forum.show_sys_progress_indicator(true);

  debug_line("Loading just created post: " + created_post, "posting");

  var params = { post: created_post, in_search: in_search };

  if(!load_created_post_ajax)
  {
    load_created_post_ajax = new Forum.AJAX();

    load_created_post_ajax.timeout = TIMEOUT;

    load_created_post_ajax.beforestart = function() { break_check_new_messages(); };
    load_created_post_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    load_created_post_ajax.onload = function(text, xml)
    {
      try
      {
        debug_line("We have loaded created post successfully", "posting");

        // remove old possible transfer file
        var elm = document.getElementById('ajax_data');
        if(elm) elm.parentNode.removeChild(elm);

        if(text != '')
        {
          elm = document.getElementById("no_posts_message");
          if(elm) elm.style.display = "none";
        }

        elm = document.getElementById("post_" + load_created_post_ajax.original_post);
        if(elm) 
        {
          var message_container = document.createElement("div");
          message_container.id = "post_" + load_created_post_ajax.created_post;
          message_container.classList.add("message_container");
          message_container.classList.add("message_container_with_offset");
          
          var reply_indicator = document.createElement("div");
          reply_indicator.classList.add("reply_indicator");
          message_container.append(reply_indicator);

          elm.parentNode.insertBefore(message_container, elm.nextSibling);
          
          message_container.insertAdjacentHTML('beforeend', text);
        }

        setTimeout(function () {
          init_lightbox_images();
          init_embedded_widgets();

          elm = document.getElementById('ajax_data');
          if(elm) 
          {
            // check for possible warning and errors
            extract_attributes(elm);
          }
          
          init_lightbox_images();
          init_embedded_widgets();
        
          // highlichting code if not highlighted yet
          var codes = post_area.getElementsByTagName('code');
          for(var i = 0; i < codes.length; i++)
          {
            if(!codes[i].classList.contains("hljs")) hljs.highlightBlock(codes[i]);
          }

          debug_line("Highlighting the message: " + load_created_post_ajax.created_post, "posting");
          set_current_post(load_created_post_ajax.created_post);

          // by using insertAdjacentHTML for a content with images
          // they are loaded not immediately, we need a timeout before
          // calcualtion of the heights
          setTimeout(init_more_buttons, 1000);
          setTimeout(init_more_buttons, 2500);

          exec_reload_nav_control('message_info_bar', load_created_post_ajax.created_post);
          exec_reload_nav_control('navigator_bar', load_created_post_ajax.created_post);
          exec_reload_online_users();
          
          if (on_loaded) on_loaded();
        }, 200);

        if (!on_loaded) Forum.show_sys_progress_indicator(false);
        
        if(messages) Forum.handle_response_messages(messages);
      }
      catch(err)
      {
        Forum.show_sys_progress_indicator(false);
        
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
    };

    load_created_post_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  } // init ajax

  load_created_post_ajax.abort();
  load_created_post_ajax.resetParams();

  load_created_post_ajax.created_post = created_post;
  load_created_post_ajax.original_post = original_post;

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    load_created_post_ajax.setPOST(p, params[p]);
  }

  load_created_post_ajax.setPOST('hash', get_protection_hash());
  load_created_post_ajax.setPOST('user_logged', user_logged);
  load_created_post_ajax.setPOST('trace_sql', trace_sql);

  load_created_post_ajax.setPOST('fpage', fpage);

  load_created_post_ajax.request("ajax/load_post.php");

  return false;
}

var load_new_posts_ajax = null;

function load_new_posts(topic, forum, highlight_message, target_url)
{
  // -1 highlighting the first new
  // -2 no highlighting
  
  var post_area = document.getElementById("post_area");
  if(!post_area) return false;

  hide_all_popups();

  Forum.show_sys_progress_indicator(true);

  var may_load_new_posts = false;
  var posts_until_end = '';
  
  var posts = document.getElementsByClassName("post_table");
  var posts_count = posts.length;
  
  posts = document.getElementsByClassName("deleted_post");
  posts_count -= posts.length;
  
  posts = document.getElementsByClassName("message_container_with_offset");
  posts_count -= posts.length;
  
  if(posts_count < posts_per_page)
  {
    may_load_new_posts = true;
    posts_until_end = posts_per_page - posts_count;
  }
  
  if(all_page_mode) 
  {
    may_load_new_posts = true;
    posts_until_end = '';
  }
  
  debug_line("Trying to load new posts, message to be highlighted: " + highlight_message + ", target_url: " + target_url, "posting");
  
  if(!may_load_new_posts)
  {
    debug_line("We may not load new posts, the page is full", "posting");
    debug_line("Let's handle the highlighting", "posting");
    
    if(!highlight_message || parseInt(highlight_message) == -2)
    {
      debug_line("No highlight message", "posting");
      Forum.show_sys_progress_indicator(false);

      return false;
    }
    
    // first new message or the message is not on the current page
    if(parseInt(highlight_message) == -1 || !document.getElementById('post_head_' + highlight_message))
    {
      debug_line("The highlight_message is not on the page: " + highlight_message, "posting");
      
      break_auto_save();
      store_unposted_message();
      delay_redirect(target_url + "&startmsg=msg"); // ensure that the message is the first on the page
      
      return false;
    }
    
    debug_line("The highlight_message is on the page: " + highlight_message, "posting");
    
    // we are on the last page but we answered not the last message
    // we have to highlight the next one after that without loading new because the page is already full
    set_current_post(highlight_message);

    exec_reload_nav_control('message_info_bar', highlight_message);
    exec_reload_nav_control('navigator_bar', highlight_message);
    exec_reload_online_users();
    
    Forum.show_sys_progress_indicator(false);

    return false;
  }
  
  debug_line("We may load new posts", "posting");
  
  var params = { tid: topic, fid: forum, last_read_message: last_message, limit: posts_until_end, posts_per_page: posts_per_page, loaded_message_count: loaded_message_count, post_count: posts_count, highlight_message: highlight_message };

  if(!load_new_posts_ajax)
  {
    load_new_posts_ajax = new Forum.AJAX();

    load_new_posts_ajax.timeout = TIMEOUT;

    load_new_posts_ajax.beforestart = function() { break_check_new_messages(); };
    load_new_posts_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    load_new_posts_ajax.onload = function(text, xml)
    {
      try
      {
        debug_line("We have loaded new posts successfully", "posting");

        unset_topic_new_markers();

        // remove old possible transfer file
        var elm = document.getElementById('ajax_data');
        if(elm) elm.parentNode.removeChild(elm);

        if(text != '')
        {
          elm = document.getElementById("no_posts_message");
          if(elm) elm.style.display = "none";
        }

        post_area.insertAdjacentHTML('beforeend', text);

        setTimeout(function () {
          init_lightbox_images();
          init_embedded_widgets();

          elm = document.getElementById('ajax_data');
          if(elm) 
          {
            // returns true if redirection required, because no new non-ignored posts loaded
            if(extract_attributes(elm))
            {
              // we do redirection only if desired gotonew, that is load_new_posts_ajax.highlight_message == -1
              if(parseInt(load_new_posts_ajax.highlight_message) == -1)
              {
                debug_line("We need to redirect because no new non-ignored posts loaded, and we want to jump to the first new", "posting");

                break_auto_save();
                store_unposted_message();
                // it happens if only ignored remained, we do not force to be the first on the next page
                delay_redirect(load_new_posts_ajax.target_url); 
                return false;
              }
              else
              {
                debug_line("We do not need to redirect because no new non-ignored posts loaded, but we do not want to jump to the first new, but just to the next.", "posting");
              }
            }
          }
          
          init_lightbox_images();
          init_embedded_widgets();
        
          // highlichting code if not highlighted yet
          var codes = post_area.getElementsByTagName('code');
          for(var i = 0; i < codes.length; i++)
          {
            if(!codes[i].classList.contains("hljs")) hljs.highlightBlock(codes[i]);
          }

          if(parseInt(load_new_posts_ajax.highlight_message) == -1) 
          {
            debug_line("We have to highlight the first new, it is: " + first_new_message, "posting");
            load_new_posts_ajax.highlight_message = first_new_message;
          }

          if(load_new_posts_ajax.highlight_message && parseInt(load_new_posts_ajax.highlight_message) != -2)
          {
            set_current_post(load_new_posts_ajax.highlight_message);
          }
          else
          {
            debug_line("No message to highlight", "posting");
          }

          // by using insertAdjacentHTML for a content with images
          // they are loaded not immediately, we need a timeout before
          // calcualtion of the heights
          setTimeout(init_more_buttons, 1000);
          setTimeout(init_more_buttons, 2500);

          exec_reload_nav_control('message_info_bar', first_new_message);
          exec_reload_nav_control('navigator_bar', first_new_message);
          exec_reload_online_users();
        }, 200);

        if(messages) Forum.handle_response_messages(messages);
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    load_new_posts_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  } // init ajax

  load_new_posts_ajax.abort();
  load_new_posts_ajax.resetParams();

  load_new_posts_ajax.last_read_message = last_message;
  load_new_posts_ajax.highlight_message = highlight_message;
  load_new_posts_ajax.target_url = target_url;

  var loaded_my_posts = document.querySelectorAll(".message_container_with_offset table.post_table");
  for(var i = 0; i < loaded_my_posts.length; i++)
  {
    load_new_posts_ajax.setPOST("exclude_posts[" + i + "]", loaded_my_posts[i].getAttribute("data-pid"));
  }
  
  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    load_new_posts_ajax.setPOST(p, params[p]);
  }

  load_new_posts_ajax.setPOST('hash', get_protection_hash());
  load_new_posts_ajax.setPOST('user_logged', user_logged);
  load_new_posts_ajax.setPOST('trace_sql', trace_sql);

  load_new_posts_ajax.setPOST('fpage', fpage);

  load_new_posts_ajax.request("ajax/load_new_posts.php");

  return false;
}

function confirm_poll_action(msg, action)
{
  hide_all_popups();

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        
        vote(action);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

function confirm_action_with_comment(msg, params)
{
  hide_all_popups();

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        
        var elm = document.getElementById("sys_user_textarea");
        if(elm) params["comment"] = elm.value;
        
        do_action(params);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        if(params.deselect_pid)
        {
          var elm = document.getElementById("post_head_" + params.deselect_pid);
          if(elm) elm.parentNode.classList.remove('selected_post_row');

          if(selected_posts[params.deselect_pid]) delete selected_posts[params.deselect_pid];
        }

        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_textarea(msg_Confirmation, msg, '', 'icon-question.gif', mbuttons, 170);

  return false;
}

var action_ajax = null;

function do_action(params)
{
  hide_actions();
  
  if(params.topic_action != "toggle_post_tag" && params.topic_action != "add_new_tag")
    hide_all_popups();
  
  if(params.topic_action == "load_version")
    set_version_loading(params.post, params.version, true);
  else if(params.topic_action == "toggle_post_tag")
    set_tag_loading(params.post, params.tag, true);
  else if(params.topic_action == "add_new_tag")
    set_new_tag_loading(params.post, true);
  else if(params.topic_action == "rate_post" ||
    params.topic_action == "reset_rating")
    show_hide_post_load(params.post, true);
  else if(params.topic_action == "add_post_to_favourites" ||
          params.topic_action == "remove_post_from_favourites")
    show_hide_post_favourite_load(params.post, true);
  else if(params.topic_action == "subscribe_to_post" ||
          params.topic_action == "unsubscribe_from_post")
    show_hide_post_subscribe_load(params.post, true);
  else if(params.topic_action == "add_attachment_to_favourites" ||
          params.topic_action == "remove_attachment_from_favourites")
    show_hide_attachment_favourite_load(params.id, true);
  else
    Forum.show_sys_progress_indicator(true);

  if(!action_ajax)
  {
    action_ajax = new Forum.AJAX();

    action_ajax.timeout = TIMEOUT;

    action_ajax.beforestart = function() { break_check_new_messages(); };
    action_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    action_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          if(response.target_url)
          {
            delay_redirect(response.target_url);
            return;
          }

          if(this.params.topic_action == "publish") 
          {
            delay_reload();
            return;
          }

          if(response.convert_action_link) convert_action_link(response.convert_action_link, this.params);
          
          if(this.params.subscribe_action == "subscribe_to_user" || this.params.subscribe_action == "unsubscribe_from_user") 
            invert_user_subscribe_action(this.params);

          if(this.params.profile_hide_action == "open_user_profile" || this.params.profile_hide_action == "hide_user_profile") 
            invert_hide_profile_action(this.params.profile_hide_action, this.params.author_name, this.params.display_author_name, this.params.uid);
          else if(this.params.profile_hide_action == "open_guest_profile" || this.params.profile_hide_action == "hide_guest_profile") 
            invert_hide_profile_action(this.params.profile_hide_action, this.params.guest_name, this.params.display_guest_name, this.params.guest_id);
            
          if(this.params.ignore_action == "put_to_ignore_list" || this.params.ignore_action == "remove_from_ignore_list") 
            invert_ignore_action(this.params.ignore_action, this.params.author_name, this.params.display_author_name, this.params.uid, this.params.uid);
          if(this.params.ignore_action == "put_guest_to_ignore_list" || this.params.ignore_action == "remove_guest_from_ignore_list") 
            invert_ignore_action(this.params.ignore_action, this.params.guest_name, this.params.display_guest_name, this.params.guest_name, this.params.guest_id);

          if(this.params.topic_action == "delete_post" || this.params.topic_action == "restore_post" || this.params.topic_action == "move_posts" ||
             this.params.topic_action == "convert_to_thematic" || this.params.topic_action == "convert_to_comment" ||
             this.params.topic_action == "convert_to_adult" || this.params.topic_action == "convert_to_nonadult") update_posts(this.params);

          if(this.params.load_auto_saved && response.message) load_message(response.message);

          if(response.rating) set_new_post_rating(this.params.post, response.rating);

          if(this.params.topic_action == "edit_message") edit_message(this.params, response);
          
          if(this.params.topic_action == "comment_message" ||
             this.params.topic_action == "add_remove_private_members" ||
             this.params.topic_action == "block_unblock_topic_users") Forum.hide_sys_lightbox();
             
          if(this.params.topic_action == "load_version") set_version_loaded(this.params.post, this.params.version, response.version_content, this.params.version_list);

          if(this.params.topic_action == "toggle_post_tag") 
          {
            set_tag_loaded(this.params.post, this.params.tag, response.tag_selected, response.selected_tags);
          }

          if(this.params.topic_action == "add_new_tag") 
          {
            new_tag_added(this.params.post, response.tag_to_select, response.added_tag, response.selected_tags);
          }
          
          if(this.params.topic_action == "delete_tags") 
          {
            Forum.update_user_tags(response.user_tags);
          }
          
          if(this.params.topic_action == "add_new_tag2") 
          {
            new_tag_added2(this.params.new_tag, response);
          }
          
          if(this.params.topic_action == "edit_tag") 
          {
            tag_edited(this.params.tgid, this.params.tag_name, response);
          }
          
          if(this.params.topic_action == "merge_tags") 
          {
            tags_merged(this.params.tgid, this.params.tag_name, response);
          }

          if(this.params.mark_read_action == "mark_topic_read") unset_topic_new_markers();
          if(this.params.mark_read_action == "mark_topic_unread") set_topic_new_markers(response.new_messages_count);

          if(typeof response.warning != "undefined") update_moderator_warning(this.params.pid, response);
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      if(this.params.topic_action == "load_version")
        set_version_loading(this.params.post, this.params.version, false);
      else if(this.params.topic_action == "toggle_post_tag")
        set_tag_loading(this.params.post, this.params.tag, false);
      else if(this.params.topic_action == "delete_restore_attachment")
        reload_attachment(this.params.attachment, this.params.nr);
      else if(this.params.topic_action == "add_new_tag")
        set_new_tag_loading(this.params.post, false);
      else if(this.params.topic_action == "rate_post" ||
              this.params.topic_action == "reset_rating")
        show_hide_post_load(this.params.post, false);
      else if(this.params.topic_action == "add_post_to_favourites" ||
              this.params.topic_action == "remove_post_from_favourites")
        show_hide_post_favourite_load(this.params.post, false);
      else if(this.params.topic_action == "subscribe_to_post" ||
              this.params.topic_action == "unsubscribe_from_post")
        show_hide_post_subscribe_load(this.params.post, false);
      else if(this.params.topic_action == "add_attachment_to_favourites" ||
              this.params.topic_action == "remove_attachment_from_favourites")
        show_hide_attachment_favourite_load(this.params.id, false);
      else
        Forum.show_sys_progress_indicator(false);
    };

    action_ajax.onerror = function(error, url, info)
    {
      if(this.params.topic_action == "load_version")
        set_version_loading(this.params.post, this.params.version, false);
      else if(this.params.topic_action == "toggle_post_tag")
        set_tag_loading(this.params.post, this.params.tag, false);
      else if(this.params.topic_action == "add_new_tag")
        set_new_tag_loading(this.params.post, false);
      else if(this.params.topic_action == "rate_post" ||
              this.params.topic_action == "reset_rating")
        show_hide_post_load(this.params.post, false);
      else if(this.params.topic_action == "add_post_to_favourites" ||
              this.params.topic_action == "remove_post_from_favourites")
        show_hide_post_favourite_load(this.params.post, false);
      else if(params.topic_action == "subscribe_to_post" ||
              params.topic_action == "unsubscribe_from_post")
        show_hide_post_subscribe_load(this.params.post, false);
      else if(this.params.topic_action == "add_attachment_to_favourites" ||
              this.params.topic_action == "remove_attachment_from_favourites")
        show_hide_attachment_favourite_load(this.params.id, false);
      else
        Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  } // init ajax

  action_ajax.abort();
  action_ajax.resetParams();

  action_ajax.params = params;

  for(var p in params)
  {
    if(!Object.prototype.hasOwnProperty.call(params, p)) continue;

    action_ajax.setPOST(p, params[p]);
  }

  var i = 0;
  var p;

  for(p in selected_posts)
  {
    if(!Object.prototype.hasOwnProperty.call(selected_posts, p)) continue;

    action_ajax.setPOST("posts[" + (i++) + "]", p);
  }

  for(p in selected_tags)
  {
    if(!Object.prototype.hasOwnProperty.call(selected_tags, p)) continue;

    action_ajax.setPOST("tags[" + (i++) + "]", p.substring(1));
  }

  action_ajax.setPOST('hash', get_protection_hash());
  action_ajax.setPOST('user_logged', user_logged);
  action_ajax.setPOST('trace_sql', trace_sql);
  action_ajax.setPOST('current_url', current_url);

  action_ajax.setPOST('user_marker', user_marker);

  action_ajax.setPOST('fpage', fpage);

  action_ajax.request("ajax/process.php");

  return false;
}

function new_message(cid, rpid, tid, subject, profiled_topic, stringent_rules)
{
  if(cid == '') cid = 'post_container_' + rpid;

  // while editing decided to write a new message
  var elm = document.getElementById('edit_mode');
  if(elm && elm.value == "1")
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() {
          Forum.hide_user_msgbox();

          focus_message_field();
        }
      }
    ];

    Forum.show_user_msgbox(msg_Warning, msg_MsgSubmitOrCancelCurrentMessage, 'icon-warning.gif', mbuttons);

    return false;
  }

  elm = document.getElementById("load_last_version");
  if(elm) elm.style.visibility = has_auto_saved_message ? "visible" : "hidden";

  add_form_to_container(cid, rpid);

  elm = document.getElementById('tid');
  if(elm)
  {
    elm.value = tid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('subject');
  if(elm)
  {
    elm.value = subject;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('profiled_topic');
  if(elm)
  {
    elm.value = profiled_topic;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('stringent_rules');
  if(elm)
  {
    elm.value = stringent_rules;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('citated_post');
  if(elm)
  {
    elm.value = "";
    elm.defaultValue = "";
  }

  elm = document.getElementById('return_post');
  if(elm)
  {
    elm.value = rpid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('special_case');
  if(elm)
  {
    if(cid == "first_post_container")
    {
      elm.value = "before_first_message";
    }
    
    if(in_search) 
    {
      elm.value = "posting_from_search";
    }

    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('stringent_rules_warning');
  if(elm)
  {
    elm.style.display = stringent_rules == 1 ? 'block' : 'none';
  }

  elm = document.getElementById('profiled_topic_row');
  if(elm)
  {
    elm.style.display = profiled_topic == 1 ? 'table-row' : 'none';
  }
  check_thematic();

  focus_message_field();

  return false;
}

function focus_message_field()
{
  var elm;

  elm = document.getElementById('post_form');
  if(elm && elm.parentNode)
  {
    elm = elm.parentNode;

    var rect = elm.getBoundingClientRect();

    window.scrollTo(0, rect.top + window.pageYOffset - 10);
  }

  try
  {
    elm = document.getElementById('message');
    if(elm)
    {
      elm.focus();

      if(elm.value.length > 0)
        elm.setSelectionRange(elm.value.length, elm.value.length);
    }
  }
  catch(err)
  {
  }
}

function answer_to_author(pid, author, tid, subject, profiled_topic, stringent_rules)
{
  var cid = 'post_container_' + pid;

  var elm = document.getElementById("load_last_version");
  if(elm) elm.style.visibility = has_auto_saved_message ? "visible" : "hidden";

  elm = document.getElementById('message');
  if(!elm) return false;

  if(elm.value != "") elm.value += "\n\n";

  if(author != '') 
  {
    elm.value += "[b]" + author;
    if(!archive_mode) elm.value += "#" + pid;
    elm.value += "[/b]\n\n";
  }

  elm.defaultValue = elm.value;

  add_form_to_container(cid, pid);

  elm = document.getElementById('tid');
  if(elm)
  {
    elm.value = tid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('subject');
  if(elm)
  {
    elm.value = subject;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('profiled_topic');
  if(elm)
  {
    elm.value = profiled_topic;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('stringent_rules');
  if(elm)
  {
    elm.value = stringent_rules;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('citated_post');
  if(elm)
  {
    elm.value += "," + pid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('return_post');
  if(elm)
  {
    elm.value = pid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('special_case');
  if(elm)
  {
    if(in_search) elm.value = "posting_from_search";

    elm.defaultValue = elm.value;
  }
  
  elm = document.getElementById('stringent_rules_warning');
  if(elm)
  {
    elm.style.display = stringent_rules == 1 ? 'block' : 'none';
  }

  elm = document.getElementById('profiled_topic_row');
  if(elm)
  {
    elm.style.display = profiled_topic == 1 ? 'table-row' : 'none';
  }
  check_thematic();

  focus_message_field();

  return false;
}

function start_gif_loading(gif)
{
  // data-loading-in-progress is used as flag
  if(gif.getAttribute('data-loading-in-progress')) 
  {
    if(gif.previousSibling && gif.previousSibling.classList.contains('gif_loading_animation'))
      gif.previousSibling.style.display = 'none';
    
    gif.classList.remove('gif_animation_active');
    
    gif.src = gif.getAttribute('data-loading-in-progress');
    
    gif.setAttribute('data-loading-in-progress', '');
    gif.classList.remove('gif_loading_progress');
    return;
  }
  
  gif.setAttribute('data-loading-in-progress', gif.src);
  gif.classList.remove('gif_animation_active');
  gif.classList.add('gif_loading_progress');
  
  gif.onerror = function(e)
  {
    if(this.previousSibling && this.previousSibling.classList.contains('gif_loading_animation'))
      this.previousSibling.style.display = 'none';

    gif.classList.add('gif_animation_active');
    gif.classList.remove('gif_loading_progress');

    this.src = VIEW_PATH + 'images/noimage.png';
  }

  gif.onload = function(e)
  {
    if(this.previousSibling && this.previousSibling.classList.contains('gif_loading_animation'))
      this.previousSibling.style.display = 'none';
    
    gif.classList.add('gif_animation_active');
    gif.classList.remove('gif_loading_progress');
  }

  setTimeout(function() {
    if(!gif.getAttribute('data-loading-in-progress')) return;
    
    if(gif.previousSibling && gif.previousSibling.classList.contains('gif_loading_animation'))
      gif.previousSibling.style.display = 'block';
    
    var new_src = gif.getAttribute('data-src').replace(/&rnd=[^&]+/, "");
    
    gif.src = new_src + (new_src.indexOf('?') != -1 ? '&' : '?') + 'rnd=' + new Date().getTime();
  }, 300);
}

function extract_selection_nodes(container, selection)
{
  for (var i = 0; i < selection.rangeCount; i++) 
  {
    var range = selection.getRangeAt(i);

    container.appendChild(range.cloneContents());
  }
}

function process_selection()
{
  var selection = window.getSelection();
  if(!selection || selection.isCollapsed || selection.rangeCount == 0 || !selection.toString()) return false;

  var parent_pid = "";
  var pid_found = "";
  var tid_found = "";
  var subject_found = "";
  var profiled_topic_found = "";
  var stringent_rules_found = "";
  var author_found = "";
  var author_ignored = null;

  var range = null;

  range = selection.getRangeAt(0);
  if(!range) return false;

  var parent_tag_container = null;
  var selection_parent = range.commonAncestorContainer;

  while(selection_parent)
  {
    if(selection_parent.nodeType == 1)
    {
      if(pid_found == "" && selection_parent.hasAttribute('data-cmid')) 
      {
        pid_found = selection_parent.getAttribute('data-cmid');
        
        if(author_found == "" && selection_parent.hasAttribute('data-author')) 
          author_found = selection_parent.getAttribute('data-author');
      }

      if(author_ignored === null && selection_parent.classList.contains('quote_wrapper'))
      {
        if(selection_parent.classList.contains('ignored_author') || selection_parent.classList.contains('strongly_ignored_author')) 
        {
          author_ignored = true;
        }
        else
        {
          author_ignored = false;
        }
      }

      // if main body reached
      if(pid_found == "" && selection_parent.hasAttribute('data-pid')) 
      {
        pid_found = selection_parent.getAttribute('data-pid');
        
        if(author_found == "" && selection_parent.hasAttribute('data-author')) 
          author_found = selection_parent.getAttribute('data-author');
      }

      // header citation
      if(tid_found == "" && selection_parent.hasAttribute('data-tid')) 
      {
        tid_found = selection_parent.getAttribute('data-tid');

        if(author_found == "" && selection_parent.hasAttribute('data-author')) 
          author_found = selection_parent.getAttribute('data-author');
      }

      if(subject_found == "" && selection_parent.hasAttribute('data-subject')) 
      {
        subject_found = selection_parent.getAttribute('data-subject');
      }

      if(profiled_topic_found == "" && selection_parent.hasAttribute('data-profiled_topic')) 
      {
        profiled_topic_found = selection_parent.getAttribute('data-profiled_topic');
      }

      if(stringent_rules_found == "" && selection_parent.hasAttribute('data-stringent_rules')) 
      {
        stringent_rules_found = selection_parent.getAttribute('data-stringent_rules');
      }

      if(parent_tag_container === null)
      {
        if((selection_parent.classList.contains("quote_wrapper")) ||
           selection_parent.classList.contains("spoiler_wrapper") ||
           selection_parent.classList.contains("media_wrapper") ||
           selection_parent.classList.contains("code_wrapper") ||
           selection_parent.tagName == 'CODE' ||
           (selection_parent.tagName == 'TABLE' && selection_parent.classList.contains("csv_table")) ||
           selection_parent.tagName == 'UL' ||
           selection_parent.tagName == 'OL'
          ) parent_tag_container = selection_parent;
      }
      
      if(selection_parent.hasAttribute('data-pid')) 
      {
        parent_pid = selection_parent.getAttribute('data-pid');
      }  
    }

    selection_parent = selection_parent.parentNode;
  }

  if(pid_found == "")
  {
    pid_found = parent_pid;
  }  
  
  if(pid_found == "" || parent_pid == "" || author_found === false || author_ignored) return false;
  
  var selection_container = document.createElement("div");

  // just single quote is selected, do not wrap it in the post author quote
  if(parent_tag_container && parent_tag_container.classList.contains("quote_wrapper"))
  {
    var tmp = document.createElement("div");
    extract_selection_nodes(tmp, selection);
    var quote_child = tmp;

    if(tmp.childNodes.length == 1 && tmp.childNodes[0].classList && tmp.childNodes[0].classList.contains('quote'))
    {
      quote_child = tmp.childNodes[0];
    }
    else if(tmp.childNodes.length == 2 &&
            tmp.childNodes[0].classList && tmp.childNodes[0].classList.contains('quote_header') &&
            tmp.childNodes[1].classList && tmp.childNodes[1].classList.contains('quote')
           )
    {
      quote_child = tmp.childNodes[1];
    }
    else
    {
      quote_child.classList.add('quote');
      quote_child.setAttribute("data-author", parent_tag_container.getAttribute("data-author"));
      
      if (parent_tag_container.hasAttribute("data-cmid"))
         quote_child.setAttribute("data-cmid", parent_tag_container.getAttribute("data-cmid"));
    }
    
    if(parent_tag_container.getAttribute("data-cmid")) {
      selection_container = quote_child;
    }
    else
    {
      var tmp = document.createElement("div");
      tmp.classList.add('quote_wrapper');
      tmp.setAttribute("data-author", parent_tag_container.getAttribute("data-author"));
      tmp.appendChild(parent_tag_container.childNodes[0].cloneNode(true));
      tmp.appendChild(quote_child);
      selection_container.appendChild(tmp);
    }
  }
  else if(parent_tag_container && parent_tag_container.classList.contains("media_wrapper"))
  {
    selection_container.appendChild(document.createTextNode(parent_tag_container.getAttribute("data-bbcode")));
  }
  else if(parent_tag_container && parent_tag_container.classList.contains("spoiler_wrapper"))
  {
    var tmp = document.createElement('div');
    extract_selection_nodes(tmp, selection);

    if(tmp.childNodes.length == 2 &&
       tmp.childNodes[0].classList && tmp.childNodes[0].classList.contains('spoiler_header') &&
       tmp.childNodes[1].classList && tmp.childNodes[1].classList.contains('spoiler')
      )
    {
      tmp.classList.add('spoiler_wrapper');
      selection_container.appendChild(tmp);
    }
    else
    {
      var spoiler_wrapper = document.createElement('div');
      spoiler_wrapper.classList.add('spoiler_wrapper');
      spoiler_wrapper.appendChild(parent_tag_container.childNodes[0].cloneNode(true));
      tmp.classList.add('spoiler');
      spoiler_wrapper.appendChild(tmp);
      selection_container.appendChild(spoiler_wrapper);
    }
  }
  else if(parent_tag_container && parent_tag_container.classList.contains("code_wrapper"))
  {
    var tmp = document.createElement('div');
    tmp.classList.add('code_wrapper');
    tmp.setAttribute('data-code', parent_tag_container.getAttribute('data-code'));
    extract_selection_nodes(tmp, selection);

    selection_container.appendChild(tmp);
  }
  else if(parent_tag_container && parent_tag_container.hasAttribute('data-code'))
  {
    var tmp = document.createElement("div");
    extract_selection_nodes(tmp, selection);

    var highlights = tmp.getElementsByClassName('code_highlight');
    for(var i = 0; i < highlights.length; i++)
    {
      highlight = highlights[i].innerHTML;
      highlight = highlight.replace(new RegExp("<span class=\"hljs-[^\"]+\">", "g"), "");
      highlight = highlight.replace(new RegExp("</span>", "g"), "");

      tmp.replaceChild(document.createTextNode("==>" + highlight + "<=="), highlights[i]);
    }

    body = tmp.innerHTML;
    body = body.replace(new RegExp("<span class=\"code_highlight\">(.*)</span>", "g"), "==>$1<==");
    body = body.replace(new RegExp("<span class=\"hljs-[^\"]+\">", "g"), "");
    body = body.replace(new RegExp("</span>", "g"), "");
    body = Forum.decode_html(body);
    selection_container.appendChild(document.createTextNode("[code=" + parent_tag_container.getAttribute('data-code') + "]" + body + "[/code]\n\n"));
  }
  else if(parent_tag_container && (parent_tag_container.tagName == 'OL' || parent_tag_container.tagName == 'UL'))
  {
    var tmp = document.createElement(parent_tag_container.tagName);
    extract_selection_nodes(tmp, selection);

    selection_container.appendChild(tmp);
  }
  else if(parent_tag_container && parent_tag_container.tagName == 'TABLE')
  {
    var table = document.createElement('table');

    var fragment = null;
    extract_selection_nodes(fragment, selection);
    if(!fragment.firstElementChild)
    {
      var td = document.createElement('td');
      td.appendChild(fragment);

      var tr = document.createElement('tr');
      tr.appendChild(td);
      
      table.appendChild(tr);
    } 
    else if(fragment.firstElementChild.tagName == "TD")
    {
      var tr = document.createElement('tr');
      tr.appendChild(fragment);
      
      table.appendChild(tr);
    }
    else if(fragment.firstElementChild.tagName == "TR")
    {
      table.appendChild(fragment);
    }    

    selection_container.appendChild(table);
  }
  else
  {
    extract_selection_nodes(selection_container, selection);
  }

  var citation_text = convert_nodes_to_bbcode(selection_container, 1);

  citation_text = citation_text.trim();
  citation_text = citation_text.replace(new RegExp("[\r\n]{2,}", "g"), "\n\n");

  if(citation_text == "") return false;

  return { "parent_pid": parent_pid, "pid_found": pid_found, "author_found" : author_found, "tid_found" : tid_found, "subject_found" : subject_found, "profiled_topic_found" : profiled_topic_found, "stringent_rules_found" : stringent_rules_found, "citation_text": citation_text };
}

function citate_post(pid, tid, subject, profiled_topic, stringent_rules)
{
  // while editing decided to citate
  var elm;

  var citation_text = "";
  var parent_pid = "";
  var pid_found = "";
  var author_found = false;

  var result = process_selection();
  //alert('pid:' + pid + ', parent_pid:' + result.parent_pid + ', pid_found:' + result.pid_found);
  if(result !== false && result.parent_pid == pid)
  {
    parent_pid = result.parent_pid;
    pid_found = result.pid_found;
    author_found = result.author_found;
    citation_text = result.citation_text;
  }
  else
  {
    elm = document.getElementById("message_text_" + pid);
    if(!elm) return false;

    var selection_parent = elm.parentNode;

    while(selection_parent)
    {
      if(selection_parent.nodeType == 1)
      {
        if(author_found === false && selection_parent.hasAttribute('data-author')) author_found = selection_parent.getAttribute('data-author');

        if(selection_parent.hasAttribute('data-pid')) 
        {
          pid_found = selection_parent.getAttribute('data-pid');
          parent_pid = selection_parent.getAttribute('data-pid');
        }
      }

      selection_parent = selection_parent.parentNode;
    }

    citation_text = convert_nodes_to_bbcode(elm.cloneNode(true), 1);
    citation_text = citation_text.trim();
    citation_text = citation_text.replace(new RegExp("[\r\n]{2,}", "g"), "\n\n");
  }
  
  if(citation_text == '')
  {
    return answer_to_author(pid, author_found, tid, subject, profiled_topic, stringent_rules);
  }
  
  if(parent_pid == '' || pid_found == '' || author_found === false) return false;

  return citate_text(parent_pid, pid_found, author_found, tid, subject, profiled_topic, stringent_rules, citation_text);
}

function citate_text(parent_pid, pid, author, tid, subject, profiled_topic, stringent_rules, text)
{
  hide_all_popups();
  
  if(!parent_pid || !pid || text == '') return false;
  
  var elm = document.getElementById("load_last_version");
  if(elm) elm.style.visibility = has_auto_saved_message ? "visible" : "hidden";

  elm = document.getElementById('message');
  if(!elm) return false;

  var cid = 'post_container_' + parent_pid;
  
  var citate = "";

  if(author != '') 
  {
    citate += "[quote=" + author;
    if(!archive_mode) citate += "#" + pid;
    citate += "]";
  }
  else             
  {
    citate += "[quote]";
  }

  citate += text + "[/quote]\n\n";
  
  // protection against double citation
  if(elm.value == citate) 
  {
    focus_message_field();

    return false;
  }  
  
  elm.value = elm.value.trim();

  if(elm.value != "") elm.value += "\n\n";
  elm.value += citate;

  elm.defaultValue = elm.value;

  add_form_to_container(cid, parent_pid);

  elm = document.getElementById('tid');
  if(elm)
  {
    elm.value = tid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('subject');
  if(elm)
  {
    elm.value = subject;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('profiled_topic');
  if(elm)
  {
    elm.value = profiled_topic;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('stringent_rules');
  if(elm)
  {
    elm.value = stringent_rules;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('citated_post');
  if(elm)
  {
    elm.value += "," + pid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('return_post');
  if(elm)
  {
    elm.value = parent_pid;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('special_case');
  if(elm)
  {
    if(in_search) elm.value = "posting_from_search";

    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('stringent_rules_warning');
  if(elm)
  {
    elm.style.display = stringent_rules == 1 ? 'block' : 'none';
  }

  elm = document.getElementById('profiled_topic_row');
  if(elm)
  {
    elm.style.display = profiled_topic == 1 ? 'table-row' : 'none';
  }
  check_thematic();

  focus_message_field();

  return false;
}

function start_editing(params)
{
  var form = document.getElementById('post_form');
  if(!form) return false;

  // a post is already being written or edited
  if(form.parentNode.id != 'form_container')
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() {
          Forum.hide_user_msgbox();

          focus_message_field();
        }
      }
    ];

    Forum.show_user_msgbox(msg_Warning, msg_MsgSubmitOrCancelCurrentMessage, 'icon-warning.gif', mbuttons);

    return false;
  }

  do_action(params);

  return false;
}

function render_color_picker()
{
  var colorMap, colors, color, html, last, x, y, i, count = 0;

	var rows = 5;
	var cols = 8;

  colorMap = [
    "000000", "Black",
    "993300", "Burnt orange",
    "333300", "Dark olive",
    "003300", "Dark green",
    "003366", "Dark azure",
    "000080", "Navy Blue",
    "333399", "Indigo",
    "333333", "Very dark gray",
    "800000", "Maroon",
    "FF6600", "Orange",
    "808000", "Olive",
    "008000", "Green",
    "008080", "Teal",
    "0000FF", "Blue",
    "666699", "Grayish blue",
    "808080", "Gray",
    "FF0000", "Red",
    "FF9900", "Amber",
    "99CC00", "Yellow green",
    "339966", "Sea green",
    "33CCCC", "Turquoise",
    "3366FF", "Royal blue",
    "800080", "Purple",
    "999999", "Medium gray",
    "FF00FF", "Magenta",
    "FFCC00", "Gold",
    "FFFF00", "Yellow",
    "00FF00", "Lime",
    "00FFFF", "Aqua",
    "00CCFF", "Sky blue",
    "993366", "Red violet",
    "FFFFFF", "White",
    "FF99CC", "Pink",
    "FFCC99", "Peach",
    "FFFF99", "Light yellow",
    "CCFFCC", "Pale green",
    "CCFFFF", "Pale cyan",
    "99CCFF", "Light sky blue",
    "CC99FF", "Plum"
  ];

  colors = [];

  for (i = 0; i < colorMap.length; i += 2) {
    colors.push({
      text: colorMap[i + 1],
      color: '#' + colorMap[i]
    });
  }

  colors.push({
    text: "No color",
    color: "transparent"
  });

  function getColorCellHtml(color, title) {
    var isNoColor = color == 'transparent';

    return (
      '<td>' +
        '<div ' +
          ' tabIndex="-1"' +
          ' onclick="insert_tag(\'[color=' + (color ? color : '') + ']\',\'[/color]\', 0)"' +
          ' style="' + (color ? 'background-color: ' + color : '') + '"' +
          ' title="' + title + '">' +
          (isNoColor ? '&#215;' : '') +
        '</div>' +
      '</td>'
    );
  }

  html = '<table class="color_table"><tbody>';
  last = colors.length - 1;

  for (y = 0; y < rows; y++) {
    html += '<tr>';

    for (x = 0; x < cols; x++) {
      i = y * cols + x;

      if (i > last) {
        html += '<td></td>';
      } else {
        color = colors[i];
        html += getColorCellHtml(color.color, color.text);
      }
    }

    html += '</tr>';
  }

  html += '</tbody></table>';

  return html;
}

function posting_active()
{
  var container = document.getElementById('form_container');
  var form = document.getElementById('post_form');

  if(!container || !form) return false;

  return !container.contains(form) || writing_message;
}

function hide_post_form(form)
{
  hide_smile_selection_area();
  
  // Edit form is closed, stop auto save and resume checking new
  break_auto_save();
  // We halted the checking new messages while writing and posting.
  // Now, we have to resume it
  activate_check_new_messages();

  var container = document.getElementById('form_container');

  if(!container) return;

  container.appendChild(form);

  var elm = document.getElementById('enter_password_row');
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById('author_row1');
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById('author_row2');
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("login_row1");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("login_row2");
  if(elm) elm.style.display = "none";

  elm = document.getElementById('password_row1');
  if(elm) elm.style.display = "none";

  elm = document.getElementById('password_row2');
  if(elm) elm.style.display = "none";

  elm = document.getElementById('author');
  if(elm)
  {
    elm.value = last_author;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('bottom_new_message');
  if(elm)
  {
    elm.style.display = "block";
  }

  writing_message = false;
  debug_line('Writing completed, freeing history state', 'history');
  window.history.replaceState({ work_stage: 1, is_active: 0 }, null, get_history_url());
}

function handle_new_tag_enter(ev, post)
{
  if(ev.keyCode == 13 || ev.keyCode == 10)
  {
    add_new_tag(post);
  }
}

function handle_enter(ev)
{
  if(ev.ctrlKey && (ev.keyCode == 13 || ev.keyCode == 10))
  {
    post_message('post_message');
  }
}

function handle_post_comment_enter(ev)
{
  if(ev.ctrlKey && (ev.keyCode == 13 || ev.keyCode == 10) && post_comment_action)
  {
    post_comment_action();
  }
}

function insert_tag(codes, codee, poff)
{
  if ((poff == null) || (poff == 'undefined')) poff = 0;

  var message = document.getElementById('message');
  if(!message) return false;

  hide_all_popups();

  message.focus();

  if (document.selection)
  {
    // ie & may be opera 8
    var rng = document.selection.createRange();
    if(rng.text)
    {
      rng.text = codes + rng.text + codee;
    }
    else
    {
      rng.text = codes + codee;
      rng.moveEnd("character", -codee.length + poff);
    }
  }
  else if (message.selectionStart ||
           message.selectionStart == '0')
  {
    // mozilla: intellegent bcodes support
    var selStart = message.selectionStart;
    var selEnd = message.selectionEnd;

    var s = message.value;
    s = s.substring(0, selStart) + codes + s.substring(selStart, selEnd)
        + codee + s.substring(selEnd, s.length);
    message.value = s;

    if (selEnd != selStart)
    {
      message.setSelectionRange(selStart, selEnd + codes.length + codee.length);
    }
    else
    {
      message.setSelectionRange(selStart + codes.length + poff, selStart + codes.length + poff);
    }
  }
  else
  {
    message.value += codes + codee;
  }

  return false;
}

var vote_ajax = null;

function vote(action)
{
  var form = document.getElementById('poll_form');
  if(!form) return;

  Forum.show_sys_progress_indicator(true);

  if(!vote_ajax)
  {
    vote_ajax = new Forum.AJAX();

    vote_ajax.timeout = TIMEOUT;

    vote_ajax.beforestart = function() { break_check_new_messages(); };
    vote_ajax.aftercomplete = function(error) { activate_check_new_messages(); };
    
    vote_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          if(response.return_post)
          {
            reload_post(response.return_post);
            
            set_current_post(response.return_post);
          }
          else if(response.target_url)
          {
            if(document.location.href.indexOf(response.target_url) != -1)
            {
              delay_reload();
            }
            else
            {
              delay_redirect(response.target_url);
            }

            return;
          }
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    vote_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  } // init ajax

  vote_ajax.abort();
  vote_ajax.resetParams();

  var formData = new FormData(form);

  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('user_marker', user_marker);
  formData.append(action, "1");
  formData.append("fpage", fpage);

  vote_ajax.setFormData(formData);

  vote_ajax.request("ajax/process.php");

  return false;
}

var unposted_message_stored = false;
function store_unposted_message()
{
  var form = document.getElementById('post_form');

  if(!form || !writing_message || in_search || form.elements['message'].value == '' || form.elements['edit_mode'].value == 1 || cancel_confirmed) 
  {
    return;
  }
  
  try 
  {
    sessionStorage.setItem('unposted_author', form.elements['author'].value);
    
    if(form.elements['user_login']) sessionStorage.setItem('unposted_user_login', form.elements['user_login'].value);
    if(form.elements['user_password']) sessionStorage.setItem('unposted_user_password', form.elements['user_password'].value);
    
    sessionStorage.setItem('unposted_message', form.elements['message'].value);
    sessionStorage.setItem('unposted_tid', form.elements['tid'].value);
    sessionStorage.setItem('unposted_subject', form.elements['subject'].value);
    sessionStorage.setItem('unposted_profiled_topic', form.elements['profiled_topic'].value);
    sessionStorage.setItem('unposted_stringent_rules', form.elements['stringent_rules'].value);
    sessionStorage.setItem('unposted_login_active', form.elements['login_active'].value);

    sessionStorage.setItem('unposted_is_thematic', form.elements['is_thematic'].checked ? 1 : 0);
    sessionStorage.setItem('unposted_is_adult', form.elements['is_adult'].checked ? 1 : 0);
    
    unposted_message_stored = true;
  } 
  catch(err) 
  {
    alert(err.message);
  }
} // store_unposted_message

function restore_unposted_message()
{
  try 
  {
    var form = document.getElementById('post_form');
    if(!form) return;

    var unposted_author = sessionStorage.getItem('unposted_author'); 
    var unposted_user_login = sessionStorage.getItem('unposted_user_login'); 
    var unposted_user_password = sessionStorage.getItem('unposted_user_password'); 

    var unposted_message = sessionStorage.getItem('unposted_message'); 
    var unposted_subject = sessionStorage.getItem('unposted_subject'); 
    var unposted_tid = sessionStorage.getItem('unposted_tid'); 
    var unposted_profiled_topic = sessionStorage.getItem('unposted_profiled_topic'); 
    var unposted_stringent_rules = sessionStorage.getItem('unposted_stringent_rules'); 
    var unposted_login_active = sessionStorage.getItem('unposted_login_active'); 
    
    var unposted_is_thematic = sessionStorage.getItem('unposted_is_thematic'); 
    var unposted_is_adult = sessionStorage.getItem('unposted_is_adult'); 
    
    sessionStorage.removeItem("unposted_message");
    sessionStorage.removeItem("unposted_subject");
    sessionStorage.removeItem("unposted_tid");
    sessionStorage.removeItem("unposted_profiled_topic");
    sessionStorage.removeItem("unposted_stringent_rules");
    sessionStorage.removeItem("unposted_login_active");
    
    sessionStorage.removeItem("unposted_is_thematic");
    sessionStorage.removeItem("unposted_is_adult");
    
    if(!unposted_message || !unposted_subject || !unposted_tid) return;
    
    if(!writing_message) new_message('first_post_container', first_message, unposted_tid, unposted_subject, unposted_profiled_topic, unposted_stringent_rules); 

    form.elements['author'].value = unposted_author;

    form.elements['message'].value = unposted_message;

    form.elements['login_active'].value = unposted_login_active;
    if(unposted_login_active) show_author_password();

    if(form.elements['user_login']) form.elements['user_login'].value = unposted_user_login;
    if(form.elements['user_password']) form.elements['user_password'].value = unposted_user_password;

    form.elements['is_thematic'].checked = unposted_is_thematic == 1 ? true : false;
    form.elements['is_adult'].checked = unposted_is_adult == 1 ? true : false;
    
    check_thematic();
  } 
  catch(e) 
  {
    alert(e.message);
  }
}

function set_current_post(pid)
{
  if(!pid)
  {
    debug_line("No current post to set");
    return false;
  }
  
  var anchor = document.getElementById('post_anchor_' + pid);
  if(!anchor) 
  {
    debug_line("Post: " + pid + " not found");
    return false;
  }
  
  if (pid == "top_new_message") 
  {
    anchor.scrollIntoView({block: "start", behavior: "auto"});
    return false;
  }

  var elm = document.getElementById('post_head_' + pid);
  if(!elm) 
  {
    debug_line("Post: " + pid + " not found");
    return false;
  }

  debug_line("Current post " + pid + " set successfully");

  var current_posts = document.getElementsByClassName("current_post");

  var i;
  for(i = 0; i < current_posts.length; i++)
  {
    current_posts[i].classList.remove('current_post');
  }

  elm.classList.add('current_post');
  anchor.scrollIntoView({block: "start", behavior: "auto"});

  ensure_anchor_visible = pid;

  return false;
}

function more_button_clicked(event)
{
  event = event || window.event;

  if(event.preventDefault)
    event.preventDefault();
  else
    event.returnValue = false;

  var _parent = this.parentNode;
  while(_parent)
  {
    if(_parent.classList.contains('message_text_more_wrapper'))
    {
      _parent.style.display = 'none';

      _parent.previousSibling.style.maxHeight = 'none';
      break;
    }

    _parent = _parent.parentNode;
  }

  return false;
}

function remove_expander(quote)
{
  quote.style.maxHeight = 'none';
  quote.style.opacity = '1.0';
  
  var cnt = quote.childNodes.length;

  if(cnt == 0) return;
  
  for(var i = cnt-1; i >= 0; i--)
  {
    if(quote.childNodes[i].classList && quote.childNodes[i].classList.contains('citate_expander')) 
    {
      quote.removeChild(quote.childNodes[i]);
    }
  }
}

function expand_citate(event)
{
  event = event || window.event;

  if(event.preventDefault)
    event.preventDefault();
  else
    event.returnValue = false;

  var _parent = this.parentNode;
  while(_parent)
  {
    if(_parent.classList.contains('quote'))
    {
      remove_expander(_parent);
    }

    if(_parent.classList.contains('message_text'))
    {
      _parent.style.maxHeight = 'none';

      _parent.nextElementSibling.style.display = 'none';
      break;
    }
    
    _parent = _parent.parentNode;
  }
}

function init_more_buttons()
{
  var _parent;
  var elm;
  var child;
  
  var post_citates = document.getElementsByClassName("quote");

  if(post_citates.length >= 0)
  {
    for(var i = 0; i < post_citates.length; i++)
    {
      _parent = post_citates[i].parentNode;

      var maxHeight = parseInt(getComputedStyle(post_citates[i]).maxHeight);

      if(post_citates[i].scrollHeight > post_citates[i].offsetHeight-8 &&
         post_citates[i].scrollHeight > maxHeight-8 &&
         !post_citates[i].expander_added)
      {
        elm = document.createElement("div");
        elm.classList.add("citate_expander");
        child = document.createElement("div");
        child.innerHTML = "...";
        Forum.addXEvent(child, 'click', expand_citate);
        elm.appendChild(child);
        post_citates[i].appendChild(elm);
        post_citates[i].expander_added = 1;
        post_citates[i].style.opacity = '1.0';
      }
    }
  }
  
  var more_buttons = document.getElementsByClassName("message_text_more");

  if(more_buttons.length >= 0)
  {
    for(var i = 0; i < more_buttons.length; i++)
    {
      _parent = more_buttons[i].parentNode;

      var maxHeight = parseInt(getComputedStyle(_parent.previousSibling).maxHeight);

      if(_parent.previousSibling.scrollHeight > _parent.previousSibling.offsetHeight &&
         _parent.previousSibling.scrollHeight > maxHeight)
      {
        _parent.style.display = 'block';
      }

      Forum.addXEvent(more_buttons[i], 'click', more_button_clicked);
    }
  }
}

function clear_selection()
{
  var selection = window.getSelection();
  if(selection) selection.removeAllRanges();
}

var last_saved_text = "";
var auto_save_active = false;

function activate_auto_save()
{
  auto_save_active = true;

  setTimeout(do_auto_save, 10000);
}

function stop_auto_save()
{
  auto_save_active = false;
}

function break_auto_save()
{
  auto_save_active = false;

  if(auto_save_ajax && auto_save_ajax.running)
  {
    auto_save_ajax.abort();
  }
}

var auto_save_ajax = null;

function do_auto_save()
{
  if(!auto_save_active) return;

  if(auto_save_ajax && auto_save_ajax.running)
  {
    return;
  }

  var form = document.getElementById('post_form');
  if(!form) return;

  if(!Forum.formDirty(form) ||
     form.elements['message'].value == '' ||
     last_saved_text == form.elements['message'].value)
  {
    setTimeout(do_auto_save, 10000);
    return;
  }

  if(!auto_save_ajax)
  {
    auto_save_ajax = new Forum.AJAX();

    auto_save_ajax.timeout = TIMEOUT;

    auto_save_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        if(response.success)
        {
          last_saved_text = form.elements['message'].value;

          has_auto_saved_message = true;

          var elm = document.getElementById("load_last_version");
          if(elm) elm.style.visibility = "visible";
        }
        else
        {
        }
      }
      catch(err)
      {
      }

      setTimeout(do_auto_save, 10000);
    };

    auto_save_ajax.onerror = function(error, url, info)
    {
      setTimeout(do_auto_save, 10000);
    };
  }

  auto_save_ajax.abort();
  auto_save_ajax.resetParams();

  auto_save_ajax.setPOST('auto_save', "1");
  auto_save_ajax.setPOST('hash', get_protection_hash());
  auto_save_ajax.setPOST('user_logged', user_logged);
  auto_save_ajax.setPOST('trace_sql', trace_sql);
  auto_save_ajax.setPOST('topic', topic_id);

  auto_save_ajax.setPOST('fpage', fpage);

  auto_save_ajax.setPOST('message', form.elements['message'].value);

  auto_save_ajax.request("ajax/process_session_readonly.php");
}

function load_message(message)
{
  var form = document.getElementById('post_form');
  if(!form) return;

  form.elements['message'].value = message;
  last_saved_text == form.elements['message'].value;

  focus_message_field();
}

function confirm_load(msg, params)
{
  hide_all_popups();

  var form = document.getElementById('post_form');
  if(!form) return false;

  if(!Forum.formDirty(form) || form.elements['message'].value == '')
  {
    do_action(params);
    return false;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        do_action(params);
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();

        focus_message_field();
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

function show_author_password()
{
  var elm = document.getElementById("enter_password_row");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("author_row1");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("author_row2");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("login_row1");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("login_row2");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("password_row1");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("password_row2");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("request_moderation_row");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("no_guests_row");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("login_active");
  if(elm) elm.value = 1;

  elm = document.getElementById("user_login");
  if(elm)
  {
    elm.value = "";
    elm.focus();
  }

  show_hide_captcha(false);

  return false;
}

function cancel_author_password()
{
  var elm = document.getElementById("enter_password_row");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("author_row1");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("author_row2");
  if(elm) elm.style.display = "table-row";

  elm = document.getElementById("login_row1");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("login_row2");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("password_row1");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("password_row2");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("request_moderation_row");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("no_guests_row");
  if(elm) elm.style.display = "none";

  elm = document.getElementById("login_active");
  if(elm) elm.value = '';

  elm = document.getElementById("author");
  if(elm)
  {
    elm.focus();
  }

  show_hide_captcha(true);

  return false;
}

function show_member_selector(area_id, title_text, selected_users_caption, buttons, width)
{
  var lbox = document.getElementById("sys_lightbox");
  if(!lbox) return;

  var title = document.getElementById("sys_lightbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var head = document.getElementById("sys_lightbox_head");
  var body = document.getElementById("sys_lightbox_body");
  var toolbar = document.getElementById("sys_lightbox_toolbar");
  var elm = document.getElementById(area_id);
  var form = document.getElementById(area_id + "_form");

  Forum.last_element_parent = null;

  if(body && head && toolbar && elm && form)
  {
    var captions = elm.getElementsByClassName("selected_users_caption");
    for(var i = 0; i < captions.length; i++)
    {
      captions[i].innerHTML = selected_users_caption;
    }

    Forum.last_element_parent = elm.parentNode;

    head.style.width = width + "px";
    body.style.width = width + "px";
    toolbar.style.width = width + "px";

    body.style.height = "auto";

    toolbar.style.display = "block";

    elm.style.display = "block";
    body.appendChild(elm);

    var button_container = document.createElement("div");
    toolbar.appendChild(button_container);

    for(var i = 0; i < buttons.length; i++)
    {
      elm = document.createElement("input");
      elm.type = "button";
      elm.className = "standard_button";
      if(buttons[i].addClass) elm.className += " " + buttons[i].addClass;
      elm.value = buttons[i].caption;
      if(buttons[i].handler) elm.onclick = buttons[i].handler;

      button_container.appendChild(elm);
    }
  }

  lbox.style.display = "table";

  if(form.elements['found_users[]']) Forum.removeAllItems(form.elements['found_users[]']);

  if(form.elements['user_to_search'])
  {
    form.elements['user_to_search'].value = "";
    form.elements['user_to_search'].focus();
  }
}

function show_tag_selection_list(pid)
{
  hide_all_popups();
  
  var elm = document.getElementById('tags_list_' + pid);
  if(elm) elm.style.display = "none";
  
  var new_tag = document.getElementById('new_tag_' + pid);
  if(new_tag) new_tag.value = '';

  elm = document.getElementById('manage_tags_list_' + pid);
  if(!elm) return;
  
  // remove any old entries
  
  var tags = elm.getElementsByClassName('tag');
  if(tags.length > 0)
  {
    for(var i = tags.length - 1; i >= 0; i--)
    {
      elm.removeChild(tags[i]);
    }
  }
  
  // add current tags
  
  var tag;
  
  var insert_marker = elm.getElementsByClassName('insert_marker');
  if(insert_marker.length > 0) insert_marker = insert_marker[0];
  
  var selected_tags = elm.getAttribute("data-selected-tags");
  selected_tags = selected_tags.split(",");
  
  for(tgid in user_tags)
  {
    tag = document.createElement('div');
    tag.classList.add('tag');
    
    if(selected_tags.indexOf(tgid) != -1) tag.classList.add('selected_tag');
    
    tag.innerHTML = user_tags[tgid];
    tag.id = 'tag_' + elm.getAttribute('data-pid') + '_' + tgid.substring(1);
    tag.title = user_tags[tgid];
    tag.setAttribute("data-pid", elm.getAttribute('data-pid'));
    tag.setAttribute("data-tgid", tgid.substring(1));
    
    elm.insertBefore(tag, insert_marker);
    
    Forum.addXEvent(tag, 'click', function (e) {
      do_action({ topic_action: "toggle_post_tag", post: this.getAttribute('data-pid'), tag: this.getAttribute('data-tgid') });
    });
  }
  
  elm.style.display = "block";
  
  if(new_tag && Forum.isEmptyObject(user_tags)) new_tag.focus();
}

function hide_manage_tags_list(pid)
{
  var elm = document.getElementById('manage_tags_list_' + pid);
  if(elm) elm.style.display = "none";

  elm = document.getElementById('tags_list_' + pid);
  if(elm) elm.style.display = "block";  
}

function set_tag_loading(post, tag, state)
{
  var elm = document.getElementById('tag_' + post + '_' + tag);
  if(!elm) return;
  
  if(state) 
  {
    elm.classList.add('loading_tag');
  }
  else      
  {
    elm.classList.remove('loading_tag');
  }
}

function rebuild_selected_tag_list(post, selected_tags)
{
  var elm = document.getElementById('tags_list_' + post);

  var tags = elm.getElementsByClassName('tag');
  if(tags.length > 0)
  {
    for(var i = tags.length - 1; i >= 0; i--)
    {
      elm.removeChild(tags[i]);
    }
  }  
  
  var tag;
  var selected_tags_string = '';
  
  for(tgid in user_tags)
  {
    if(!selected_tags || selected_tags.indexOf(tgid) == -1) continue;
    
    tag = document.createElement('div');
    tag.classList.add('tag');
    tag.innerHTML = '#' + user_tags[tgid];
    tag.title = msg_AssignTags;
    tag.onclick = function () { show_tag_selection_list(post); };
    
    if(selected_tags_string != '') selected_tags_string += ',';
    selected_tags_string += tgid;

    elm.insertBefore(tag, elm.lastChild);
  }
  
  elm = document.getElementById('manage_tags_list_' + post);
  if(!elm) return;
  
  elm.setAttribute('data-selected-tags', selected_tags_string);  
} // rebuild_selected_tag_list

function set_tag_loaded(post, tag, tag_selected, selected_tags)
{
  var elm = document.getElementById('tag_' + post + '_' + tag);
  if(!elm) return;
  
  if(tag_selected) elm.classList.add('selected_tag');
  else             elm.classList.remove('selected_tag');
  
  rebuild_selected_tag_list(post, selected_tags);
} // set_tag_loaded

function set_new_tag_loading(post, state)
{
  var elm = document.getElementById('add_new_tag_' + post);
  if(!elm) return;
  
  if(state) 
  {
    elm.classList.add('loading_tag');
  }
  else
  {
    elm.classList.remove('loading_tag');
  }    
}

function new_tag_added(post, tag_to_select, added_tag, selected_tags)
{
  var elm;
  
  if(added_tag)
  {
    if(tag_to_select == '') return;
    
    elm = document.getElementById('manage_tags_list_' + post);
    if(!elm) return;
    
    var tag;
    
    var insert_marker = elm.getElementsByClassName('insert_marker');
    if(insert_marker.length > 0) insert_marker = insert_marker[0];
    
    tag = document.createElement('div');
    tag.classList.add('tag');
    tag.classList.add('selected_tag');
    
    tag.innerHTML = added_tag;
    tag.id = 'tag_' + elm.getAttribute('data-pid') + '_' + tag_to_select;
    tag.title = added_tag;
    tag.setAttribute("data-pid", post);
    tag.setAttribute("data-tgid", tag_to_select);
    
    elm.insertBefore(tag, insert_marker);
    
    Forum.addXEvent(tag, 'click', function (e) {
      do_action({ topic_action: "toggle_post_tag", post: this.getAttribute('data-pid'), tag: this.getAttribute('data-tgid') });
    });
    
    user_tags['#' + tag_to_select] = added_tag;
  }
  else if(tag_to_select)
  {
    elm = document.getElementById('tag_' + post + '_' + tag_to_select);
    if(!elm) return;
    
    elm.classList.add('selected_tag');
  }
  
  rebuild_selected_tag_list(post, selected_tags);
  
  elm = document.getElementById('new_tag_' + post);
  if(!elm) return;
  
  elm.value = '';
  elm.focus();
} // new_tag_added

function add_new_tag(post)
{
  var elm = document.getElementById('new_tag_' + post);
  if(!elm) return;
  
  if(elm.value == '')
  {
    elm.focus();
    return;
  }
  
  do_action({ topic_action: "add_new_tag", post: post, new_tag: elm.value });
} // add_new_tag

function paste_text()
{
  if(window.clipboardData)
  {
    clipText = window.clipboardData.getData('Text');
    insert_tag('', clipText, clipText.length);
    return false;
  }
  
  if(!navigator.clipboard)
  {
    document.execCommand('paste');
    return false;
  }
  
  navigator.clipboard.readText().then(function (clipText) { insert_tag('', clipText, clipText.length); });
} // paste_text

function check_thematic()
{
  var editor = document.getElementById('post_message_table');
  if(!editor) return;  

  var elm = elm = document.getElementById('profiled_topic');
  if(!elm || elm.value == 0) 
  {
    editor.classList.remove("thematic_post");
    editor.classList.remove("comment_post");
    return;
  }

  elm = document.getElementById('is_thematic');
  if(!elm) 
  {
    editor.classList.remove("thematic_post");
    editor.classList.remove("comment_post");
    return;
  }
  
  if(elm.checked) 
  {
    editor.classList.add("thematic_post");
    editor.classList.remove("comment_post");
  }
  else
  {
    editor.classList.add("comment_post");
    editor.classList.remove("thematic_post");
  }  
} // check_thematic

function prepare_post_for_navigation(link)
{
  var ev = window.event;  // Event object 'ev'
  var key = ev.which || ev.keyCode; // Detecting keyCode

  var ctrl = ev.ctrlKey ? ev.ctrlKey : ((key === 17) ? true : false);
  
  if(ctrl) return true;
  
  Forum.show_sys_progress_indicator(true);

  store_unposted_message();
  
  redirection_in_pogress = true;

  break_check_new_messages();
  
  return true;
} // prepare_post_for_navigation

function select_and_do(dlink, params)
{
  select_current(dlink);
  
  do_action(params);

  return false;
}

function select_and_confirm(msg, dlink, params)
{
  select_current(dlink);

  params["deselect_pid"] = dlink.getAttribute("data-pid");

  confirm_action_with_comment(msg, params);

  return false;
}

function select_current(dlink)
{
  var elm;

  for(var p in selected_posts)
  {
    elm = document.getElementById("post_head_" + p);
    if(!elm) continue;

    elm.parentNode.classList.remove('selected_post_row');
    delete selected_posts[p];
  }

  var pid = dlink.getAttribute("data-pid");

  elm = document.getElementById("post_head_" + pid);
  if(elm) elm.parentNode.classList.add('selected_post_row');

  selected_posts[pid] = 1;
}

function delete_restore_attachment(btn, att, nr)
{
  if(!att) return false;

  if(typeof nr == 'undefined') nr = '';

  do_action({ topic_action: 'delete_restore_attachment', attachment: att, nr: nr });

  return false;
}

function adjust_scroll_position()
{
  debug_line("Adjusting initial scroll position", 'history');
  
  if(user_interaction_happened)
  {
    debug_line("User interaction happened (" + user_interaction_happened + ") during loading, no adjusting will be done", 'history');
    return;
  }
  
  if(refresh_or_history_navigation)
  {
    debug_line("It is a page refresh or history navigation with reload, try to restore the scroll position", 'history');

    var scrollpos = sessionStorage.getItem('scroll_' + get_hash(document.location.href));
    if(scrollpos > 0) 
    {
      window.scrollTo(0, scrollpos);
      debug_line("Position restored: " + scrollpos, 'history');
    }
    else
    {
      debug_line("No saved position", 'history');
    }
  }
  else
  {
    debug_line("It is a normal navigation, trying to highlight the current post", 'history');
    set_current_post(ensure_anchor_visible);
  }
}

var user_interaction_happened = false;

function setup_user_interaction() {
  Forum.addXEvent(window, 'keydown', function (ev) { 
    if(!user_interaction_happened) debug_line('User interaction detected: keydown', 'history');
    user_interaction_happened = "keydown";
  });
  Forum.addXEvent(window, 'mousedown', function (ev) { 
    if(!user_interaction_happened) debug_line('User interaction detected: mousedown', 'history');
    user_interaction_happened = "mousedown";
  });
  Forum.addXEvent(window, 'touchstart', function (ev) { 
    if(!user_interaction_happened) debug_line('User interaction detected: touchstart', 'history');
    user_interaction_happened = "touchstart";
  });
  Forum.addXEvent(window, 'wheel', function (ev) { 
    if(!user_interaction_happened) debug_line('User interaction detected: wheel', 'history');
    user_interaction_happened = "wheel";
  });
}

Forum.addXEvent(window, 'resize', function () { 
  init_more_buttons();
});

get_history_url = function () {
  return final_url;
};

Forum.addXEvent(window, 'DOMContentLoaded', function () {
  debug_line("Event 'DOMContentLoaded' fired", 'history');

  init_lightbox_images();
  init_more_buttons();

  debug_line("Topic history intialization", 'history');
  window.history.scrollRestoration = 'manual';

  if(window.history.state && (window.history.state.initial_stage || window.history.state.work_stage))
  {
    debug_line("Refresh or history back, reusing state", 'history');
    refresh_or_history_navigation = true;
    
    // we deactivate the current step if refresh was done while writing or previewing of the image
    if(window.history.state.work_stage)
    {
      debug_line("Refresh was done while image preview or writing, deactivate state", 'history');
      window.history.replaceState({ work_stage: 1, is_active: 0 }, null, get_history_url());
    }
  }
  else
  {
    debug_line("Explicit entrance to the page, replacing default and adding work state", 'history');
    window.history.replaceState({ inital_stage: 1, is_active: 0 }, null, get_history_url());
    window.history.pushState({ work_stage: 1, is_active: 0 }, null, get_history_url());
  }
  
  debug_line('Go-back action put to the stack', 'history');
  history_undo_actions_stack.push(function () {
    debug_line("Doing back", 'history');
    window.history.back();
  });

  adjust_scroll_position();
  
  setup_user_interaction();
});

Forum.addXEvent(window, 'load', function () {
  // must be repeated in after load when the picture sizes are known
  init_more_buttons();

  setTimeout(function() {
    debug_line("Event 'onload' fired", 'history');
    
    adjust_scroll_position();
    user_interaction_happened = "adjusting completed";
    
    sessionStorage.removeItem('scroll_' + get_hash(document.location.href));
    
    if(typeof startup_action == "function") startup_action();
  }, 300);
  
  Forum.addXEvent(window, 'beforeunload', function (e) {
    sessionStorage.setItem('scroll_' + get_hash(document.location.href), window.scrollY);
    
    var form = document.getElementById('post_form');
    if(!form || !writing_message || !Forum.formDirty(form) || form.elements['message'].value == '' || cancel_confirmed || unposted_message_stored) return undefined;

    Forum.show_sys_progress_indicator(false);
    
    var confirmationMessage = msg_MsgConfirmPostCancel;

    (e || window.event).returnValue = confirmationMessage; //Gecko + IE
    return confirmationMessage; //Gecko + Webkit, Safari, Chrome etc.
  });
});

function handle_writing_cancel()
{
  debug_line("Handle cancelling writing", 'history');
  
  // doing 
  var form = document.getElementById('post_form');
  if(!form) return;

  // The url might have been changed, update it  
  window.history.replaceState(window.history.state, null, get_history_url());

  if(cancel_confirmaction_in_progress || cancel_confirmed || !Forum.formDirty(form) || form.elements['message'].value == '')
  {
    Forum.hide_user_msgbox(true);
    cancel_confirmed = false;
    cancel_confirmaction_in_progress = false;

    form.elements['edit_mode'].value = '';
    form.elements['edit_mode'].defaultValue = '';
    form.elements['message'].value = '';
    form.elements['message'].defaultValue = '';
    form.elements['edited_post'].value = '';
    form.elements['edited_post'].defaultValue = '';
    form.elements['citated_post'].value = '';
    form.elements['citated_post'].defaultValue = '';
    form.elements['return_post'].value = '';
    form.elements['return_post'].defaultValue = '';
    form.elements['special_case'].value = '';
    form.elements['special_case'].defaultValue = '';
    form.elements['profiled_topic'].value = '';
    form.elements['profiled_topic'].defaultValue = '';
    form.elements['stringent_rules'].value = '';
    form.elements['stringent_rules'].defaultValue = '';
    form.elements['login_active'].value = '';
    form.elements['login_active'].defaultValue = '';
    
    if(form.elements['user_login']) 
    {
      form.elements['user_login'].value = '';
      form.elements['user_login'].defaultValue = '';
    }
    if(form.elements['user_password']) 
    {
      form.elements['user_password'].value = '';
      form.elements['user_password'].defaultValue = '';
    }
    
    form.elements['is_thematic'].checked = false;
    form.elements['is_thematic'].defaultChecked = false;
    form.elements['is_adult'].checked = false;
    form.elements['is_adult'].defaultChecked = false;

    form.reset();
    reset_attachment_fields_and_slots();

    hide_post_form(form);

    // cut off the forward history
    debug_line('No changes, just cutting forward history off', 'history');
    window.history.pushState({ work_stage: 1, is_active: 0 }, null, get_history_url());
      
    debug_line('Go-back action put to the stack', 'history');
    history_undo_actions_stack.push(function () {
      debug_line('Doing back', 'history');
      window.history.back();
    });

    set_current_post(ensure_anchor_visible);
  }
  else
  {
    // remain active and ask confirmation
    debug_line('Asking to cofirm cancelling', 'history');
    window.history.pushState({ work_stage: 1, is_active: 1 }, null, get_history_url());
    debug_line('Cancel action put to the stack', 'history');
    history_undo_actions_stack.push(handle_writing_cancel);

    confirm_cancel(form);
  }
}

