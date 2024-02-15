var lookup_entries_ajax = null;

function lookup_entries(action, fld, ev)
{
  var lst = document.getElementById(fld.id + "_lookup");

  if(fld.value.length < 2 || !lst) return true;

  if(ev.keyCode == 27 || ev.keyCode == 13) // Esc
  {
    return false;
  }

  if(ev.keyCode == 40 || ev.keyCode == 38)
  {
    if(lst.value) lst.focus();
    return false;
  }

  fld.classList.add("field_lookup_loading");

  if(!lookup_entries_ajax)
  {
    lookup_entries_ajax = new Forum.AJAX();

    lookup_entries_ajax.timeout = TIMEOUT;

    lookup_entries_ajax.beforestart = function() { break_check_new_messages(); };
    lookup_entries_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    lookup_entries_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(!response.success) 
        {
          this.fld.classList.remove("field_lookup_loading");
          return;
        }

        // remove old entries

        for(var i = this.lst.length - 1; i >= 0 ; i--)
        {
          this.lst.options[i] = null;
        }

        var found = false;
        if(response.found_entries && !Forum.isEmptyObject(response.found_entries))
        {
          for(var u in response.found_entries)
          {
            var option = new Option(response.found_entries[u],
                                    response.found_entries[u],
                                    false, mustAdjustMultiSelect() ? false : !found
                                   );
            this.lst.options[this.lst.options.length] = option;
            found = true;
          }
        }

        if(found)
        {
          this.lst.parentNode.style.display = "block";
        }
        else
        {
          this.lst.parentNode.style.display = "none";
        }
        
        Forum.fireEvent(this.lst, 'show');
        Forum.fireEvent(this.lst, 'change');
      }
      catch(err)
      {
      }
      
      this.fld.classList.remove("field_lookup_loading");
    };

    lookup_entries_ajax.onerror = function(error, url, info)
    {
      this.fld.classList.remove("field_lookup_loading");
    };
  }

  lookup_entries_ajax.abort();
  lookup_entries_ajax.resetParams();

  lookup_entries_ajax.fld = fld;
  lookup_entries_ajax.lst = lst;

  lookup_entries_ajax.setPOST(action, "1");
  lookup_entries_ajax.setPOST('hash', get_protection_hash());
  lookup_entries_ajax.setPOST('user_logged', user_logged);
  lookup_entries_ajax.setPOST('trace_sql', trace_sql);
  lookup_entries_ajax.setPOST('lookup_string', fld.value);

  lookup_entries_ajax.request("ajax/process.php");

  return true;
} // lookup_entries

function filter_entries(fld, ev)
{
  var lst = document.getElementById(fld.id + "_lookup");

  if(ev.keyCode == 27) // Esc
  {
    fld.value = "";
  }

  if(ev.keyCode == 40 || ev.keyCode == 38)
  {
    lst.focus();

    if (!lst.value) 
    {
      lst.selectedIndex = 0;
    }
    
    return false;
  }
  
  lst.selectedIndex = -1;

  for (let i = 0; i < lst.options.length; i++) {
    if (fld.value == "") {
      lst.options[i].style.display = "block";
      continue;
    }
    
    if (lst.options[i].text.indexOf(fld.value) == 0) {
      lst.options[i].style.display = "block";
      
      if (lst.selectedIndex == -1) {
        if (!mustAdjustMultiSelect()) lst.options[i].selected = true;  
      }
    } else {
      lst.options[i].style.display = "none";
    }
  }

  Forum.fireEvent(lst, 'show');
  Forum.fireEvent(lst, 'change');
}

function lookup_existing_topics(fld, ev, fid)
{
  var lst = document.getElementById(fld.id + "_lookup");

  if(fld.value.length < 3 || !lst) return true;

  if(ev.keyCode == 27 || ev.keyCode == 13) // Esc
  {
    return false;
  }

  if(ev.keyCode == 40 || ev.keyCode == 30)
  {
    if(lst.value) lst.focus();
    return false;
  }

  fld.classList.add("field_lookup_loading");

  if(!lookup_entries_ajax)
  {
    lookup_entries_ajax = new Forum.AJAX();

    lookup_entries_ajax.timeout = TIMEOUT;

    lookup_entries_ajax.beforestart = function() { break_check_new_messages(); };
    lookup_entries_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    lookup_entries_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(!response.success) 
        {
          this.fld.classList.remove("field_lookup_loading");
          return;
        }

        // remove old entries

        for(var i = this.lst.length - 1; i >= 0 ; i--)
        {
          this.lst.options[i] = null;
        }

        var found = false;
        if(response.found_entries && !Forum.isEmptyObject(response.found_entries))
        {
          for(var u in response.found_entries)
          {
            var option = new Option(response.found_entries[u].name,
                                    response.found_entries[u].tid,
                                    false, mustAdjustMultiSelect() ? false : !found
                                   );
            this.lst.options[this.lst.options.length] = option;
            found = true;
          }
        }

        if(found)
        {
          this.lst.parentNode.style.display = "block";
        }
        else
        {
          this.lst.parentNode.style.display = "none";
        }
        
        Forum.fireEvent(this.lst, 'show');
        Forum.fireEvent(this.lst, 'change');
      }
      catch(err)
      {
      }
      
      this.fld.classList.remove("field_lookup_loading");
    };

    lookup_entries_ajax.onerror = function(error, url, info)
    {
      this.fld.classList.remove("field_lookup_loading");
    };
  }

  lookup_entries_ajax.abort();
  lookup_entries_ajax.resetParams();

  lookup_entries_ajax.fld = fld;
  lookup_entries_ajax.lst = lst;

  lookup_entries_ajax.setPOST("check_existing_topics", "1");
  lookup_entries_ajax.setPOST('hash', get_protection_hash());
  lookup_entries_ajax.setPOST('user_logged', user_logged);
  lookup_entries_ajax.setPOST('trace_sql', trace_sql);
  lookup_entries_ajax.setPOST('lookup_string', fld.value);
  lookup_entries_ajax.setPOST('forum', fid);

  lookup_entries_ajax.request("ajax/process.php");

  return true;
} // lookup_existing_topics

function lookup_handle_enter(eid, ev) 
{
  if(ev.keyCode == 13) 
  {
    var lst = document.getElementById(eid + "_lookup");
    if (!lst || !lst.value) return true;

    lookup_apply_selection(eid);
    return false;
  }
}

function show_lookup_list(eid)
{
  var lst = document.getElementById(eid + "_lookup");
  if(!lst) return;

  lst.parentNode.style.display = "block";

  Forum.fireEvent(lst, 'show');
}

function lookup_delayed_hide(eid)
{
  var elm = document.getElementById(eid);
  if(!elm) return;

  var lst = document.getElementById(eid + "_lookup");
  if(!lst) return;

  setTimeout(function () { if(document.activeElement != lst && document.activeElement != elm) lst.parentNode.style.display = "none"; }, 500);
}

function lookup_apply_selection_if_active(eid)
{
  var elm = document.getElementById(eid);
  var lst = document.getElementById(eid + "_lookup");

  if(!elm || !lst) return;

  if(document.activeElement != lst) return;
    
  elm.value = lst.value;

  if(eid == "topic_name" && (matches = elm.value.match(/^\[#(\d+)\].*/)))
  {
    var t = document.getElementById("tid");
    if(t) t.value = matches[1];
  }

  if(typeof user_esc_handler == 'function') user_esc_handler();
  
  if(eid == "topic_name") 
  {
    elm.setSelectionRange(0, 0);
  }

  elm.focus();
}

function lookup_apply_selection(eid)
{
  var elm = document.getElementById(eid);
  var lst = document.getElementById(eid + "_lookup");

  if(!elm || !lst) return;

  elm.value = lst.value;
  
  if(matches = elm.value.match(/^\[#(\d+)\].*/))
  {
    var t = document.getElementById("tid");
    if(t) t.value = matches[1];
  }
  
  if(typeof user_esc_handler == 'function') user_esc_handler();

  elm.focus();
}

var appeal_author_lookup_active = false;

function lookup_appeal_authors(message, str)
{
  var lst = document.getElementById("author_lookup");
  if(!lst) return false;

  if(str.length < 2) 
  {
    lst.parentNode.parentNode.style.display = "none";
    appeal_author_lookup_active = false;
    return true;
  }
  
  var symbol = str.substring(0, 1);
  var author = str.substring(1, str.length);
  
  lst.symbol = symbol;
  lst.author = author;

  if(!lookup_entries_ajax)
  {
    lookup_entries_ajax = new Forum.AJAX();

    lookup_entries_ajax.timeout = TIMEOUT;

    lookup_entries_ajax.beforestart = function() { break_check_new_messages(); };
    lookup_entries_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    lookup_entries_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(!response.success) 
        {
          return;
        }

        // remove old entries

        for(var i = this.lst.length - 1; i >= 0 ; i--)
        {
          this.lst.options[i] = null;
        }

        var found = false;
        if(response.found_entries && !Forum.isEmptyObject(response.found_entries))
        {
          for(var u in response.found_entries)
          {
            var option = new Option(response.found_entries[u],
                                    response.found_entries[u],
                                    false, mustAdjustMultiSelect() ? false : !found
                                   );
            this.lst.options[this.lst.options.length] = option;
            found = true;
          }
        }

        if(found)
        {
          if(!appeal_author_lookup_active)
          {
            var message_rect = message.getBoundingClientRect();

            var caret = getCaretCoordinates(message, message.selectionEnd);
            this.lst.parentNode.parentNode.style.top = (caret.top + Math.round(1.6*caret.height) - message.scrollTop) + "px";
             
            var left = caret.left - 20;
            this.lst.parentNode.parentNode.style.left = left + "px";

            this.lst.parentNode.parentNode.style.display = "block";
            
            var lst_rect = lst.getBoundingClientRect();
            
            if(left + lst_rect.width > message_rect.width - Math.round(0.8*caret.height))
            {
              left = message_rect.width - Math.round(0.8*caret.height) - lst_rect.width;
            }
            
            if(left < Math.round(0.8*caret.height))
            {
              left = Math.round(0.8*caret.height);
            }
            
            this.lst.parentNode.parentNode.style.left = left + "px";
          }
          
          appeal_author_lookup_active = true;
        }
        else
        {
          this.lst.parentNode.parentNode.style.display = "none";
          appeal_author_lookup_active = false;
        }
        
        Forum.fireEvent(this.lst, 'show');
        Forum.fireEvent(this.lst, 'change');
      }
      catch(err)
      {
      }
    };

    lookup_entries_ajax.onerror = function(error, url, info)
    {
    };
  }

  lookup_entries_ajax.abort();
  lookup_entries_ajax.resetParams();

  lookup_entries_ajax.lst = lst;

  lookup_entries_ajax.setPOST("search_users", "1");
  lookup_entries_ajax.setPOST('hash', get_protection_hash());
  lookup_entries_ajax.setPOST('user_logged', user_logged);
  lookup_entries_ajax.setPOST('trace_sql', trace_sql);
  lookup_entries_ajax.setPOST('lookup_string', author);

  lookup_entries_ajax.request("ajax/process.php");

  return true;
} // lookup_appeal_authors

function hide_appeal_authors_lookup()
{
  var lst = document.getElementById("author_lookup");
  if(!lst) return false;
  
  lst.parentNode.parentNode.style.display = "none";
  appeal_author_lookup_active = false;
  
  return true;
} // hide_appeal_authors_lookup

function focus_appeal_authors_lookup()
{
  var lst = document.getElementById("author_lookup");
  if(!lst) return false;
  
  lst.focus();

  return false;
}

function handle_appeal_author_enter(ev) 
{
  if(ev.keyCode == 13) 
  {
    insert_appeal_author(false);
    return false;
  }
  
  return false;
}

function insert_appeal_author(only_if_active)
{
  var lst = document.getElementById("author_lookup");
  if(!lst) return false;

  var message = document.getElementById("message");
  if(!message) return false;
  
  if(only_if_active && document.activeElement != lst) return false;

  hide_appeal_authors_lookup();
  
  message.focus();
  
  if(message.selectionStart ||
     message.selectionStart == '0')
  {
    var selStart = message.selectionStart;
    var selEnd = message.selectionEnd;

    var s = message.value;
    
    s = s.substring(0, selStart - lst.author.length) + lst.value + lst.symbol + s.substring(selEnd, s.length);
    
    message.value = s;

    message.setSelectionRange(selEnd + lst.value.length + 1 - lst.author.length, selEnd + lst.value.length + 1 - lst.author.length);
  }
  
  return false;
}

function check_personal_appeal(ev)
{
  ev = ev || window.event;  // Event object 'ev'
  var key = ev.which || ev.keyCode; // Detecting keyCode

  if(appeal_author_lookup_active && (key == 40 || key == 38)) // down and up
  {
    focus_appeal_authors_lookup();
    ev.stopPropagation();
    ev.preventDefault();
    return false;
  }

  if(appeal_author_lookup_active && (key == 13)) // enter
  {
    insert_appeal_author(false);
    ev.stopPropagation();
    ev.preventDefault();
    return false;
  }

  if(key == 27) // esc
  {
    hide_appeal_authors_lookup();
    ev.stopPropagation();
    ev.preventDefault();
    return false;
  }
  
  if(key == 8 || key == 46) // backspace 
  {
    hide_appeal_authors_lookup();
  }
}

function check_personal_appeal2(ev)
{
  ev = ev || window.event;  // Event object 'ev'
  var key = ev.which || ev.keyCode; // Detecting keyCode

  var ctrl = ev.ctrlKey ? ev.ctrlKey : ((key === 17) ? true : false);

  if(key == 27 || // esc
     key == 8  || // backspace
     key == 46 || // delete
     key == 13 || // enter
     key == 16 || // shift
     key == 17 || // ctrl
     key == 18 || // ctrl
     key == 37 || // left
     key == 39 || // right
     key == 40 || // down
     key == 38 || // up
     key == 35 || // home
     key == 36 || // end
     key == 33 || // page up
     key == 34 || // page down
     (ctrl && key == 86) || // ctrl+V
     (ctrl && key == 67)    // ctrl+C
    ) 
  {
    return null;
  }

  var message = document.getElementById("message");
  if(!message) return null;

  var stream = "";
  
  if (message.selectionStart || message.selectionStart == '0')
  {
    var selStart = message.selectionStart;
    var selEnd = message.selectionEnd;

    var stream = message.value;
    stream = stream.substring(0, selStart);
  }
  else
  {
    stream = message.value;
  }

  var assertion = "(?<!" + "[^\\s\\.,;&:!\\=\\?\\-\\+\\(\\)\\[\\]\\{\\}\\/\\*'«»\"]" + ")";
  assertion = "";
  
  var re = new RegExp(assertion + "([@%][^ \n\r][^@%\\t\\r\\n]{1,})$", "");
  var matches = re.exec(stream);
  if(matches && matches[1].trim().length > 2)
  {
    //alert('match: ' + stream);
    lookup_appeal_authors(message, matches[1]);
  }
  else
  {
    //alert('no match: ' + stream);
    lookup_appeal_authors(message, "");
  }
  
  return null;
}

function forum_lookup_handle_enter(eid, ev)
{
  if(ev.keyCode != 13) return;

  lookup_goto_forum(eid);
}

function reset_forum_selector(eid)
{
  var elm = document.getElementById(eid);
  if (elm) elm.value = "";
  
  elm = document.getElementById(eid + "_lookup");
  if (elm) 
  {
    elm.selectedIndex = -1;

    for (let i = 0; i < elm.options.length; i++) 
    {
      elm.options[i].style.display = "block";
    }
  }

  Forum.fireEvent(elm, 'show');
  Forum.fireEvent(elm, 'change');
}

function lookup_goto_forum(eid)
{
  var lst = document.getElementById(eid + "_lookup");
  if (!lst || !lst.value) return;

  lst.parentNode.style.display = "none";
  
  Forum.show_sys_progress_indicator(true);

  document.location.href = lst.value;
}

function lookup_goto_forum_if_active(eid)
{
  var lst = document.getElementById(eid + "_lookup");
  if (!lst || !lst.value) return;

  if(document.activeElement != lst) return;

  lst.parentNode.style.display = "none";

  Forum.show_sys_progress_indicator(true);

  document.location.href = lst.value;
}

function lookup_move_to_forum(eid, params)
{
  var lst = document.getElementById(eid + "_lookup");
  if (!lst || !lst.value) return;

  params.target_forum = lst.value;
  
  do_action(params);
}

function lookup_move_to_forum_if_active(eid, params)
{
  var lst = document.getElementById(eid + "_lookup");
  if (!lst || !lst.value) return;

  if(document.activeElement != lst) return;

  params.target_forum = lst.value;

  do_action(params);
}

function forum_move_handle_enter(eid, ev, params)
{
  if(ev.keyCode != 13) return;

  lookup_move_to_forum(eid, params);
}
