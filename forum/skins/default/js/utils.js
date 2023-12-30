//----------------------------------------------------
Forum.last_element_parent = null;
Forum.on_lightbox_close = null;
//----------------------------------------------------
var hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F");
//----------------------------------------------------
function rgb2hex(rgb)
{
  matches = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);

  if(matches)
    return "#" + hex(matches[1]) + hex(matches[2]) + hex(matches[3]);
  else
    return rgb;
}
//----------------------------------------------------
function hex(x)
{
  return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}
//----------------------------------------------------
function browser_class(u)
{
  var
  ua = u.toLowerCase(),
  is = function (t) {
      return ua.indexOf(t) > -1
  },
  g = 'gecko',
  w = 'webkit',
  s = 'safari',
  o = 'opera',
  m = 'mobile',
  h = document.documentElement,
  body = document.getElementsByTagName("body")[0];
  b = [(!(/opera|webtv/i.test(ua)) && /msie\s(\d)/.test(ua)) ? ('ie ie' + RegExp.$1) : is('firefox/2') ? g + ' ff2' : is('firefox/3.5') ? g + ' ff3 ff3_5' : is('firefox/3.6') ? g + ' ff3 ff3_6' : is('firefox/3') ? g + ' ff3' : is('gecko/') ? g : is('opera') ? o + (/version\/(\d+)/.test(ua) ? ' ' + o + RegExp.$1 : (/opera(\s|\/)(\d+)/.test(ua) ? ' ' + o + RegExp.$2 : '')) : is('konqueror') ? 'konqueror' : is('blackberry') ? m + ' blackberry' : is('android') ? m + ' android' : is('chrome') ? w + ' chrome' : is('iron') ? w + ' iron' : is('applewebkit/') ? w + ' ' + s + (/version\/(\d+)/.test(ua) ? ' ' + s + RegExp.$1 : '') : is('mozilla/') ? g : '', is('j2me') ? m + ' j2me' : is('iphone') ? m + ' iphone' : is('ipod') ? m + ' ipod' : is('ipad') ? m + ' ipad' : is('mac') ? 'mac' : is('darwin') ? 'mac' : is('webtv') ? 'webtv' : is('win') ? 'win' + (is('windows nt 6.0') ? ' vista' : '') : is('freebsd') ? 'freebsd' : (is('x11') || is('linux')) ? 'linux' : '', 'js'];

  c = b.join(' ');

  body .className += ' ' + c; // setting the body class

  h.className = h.className.replace("no-js",""); // removing the class from html tag
  h.className += ' ' + "js"; // adding new class - js

  return c;
}; // browser_class
//----------------------------------------------------
function get_hash(str)
{
  var hash = 0, i;
  if (str.length === 0) return hash;

  for (i = 0; i < str.length; i++) 
  {
    hash += str.charCodeAt(i);
  }
  
  hash = hash.toString(10);
  
  var prepad_ln = 12 - hash.length;
  for(i = 0; i < prepad_ln; i++)
  {
    hash = '0' + hash;
  }

  return hash;
} // get_hash
//----------------------------------------------------
function get_agent_hash() 
{
  var nav = window.navigator;

  var screen = window.screen;
  var guid = get_hash(nav.userAgent);
  guid += nav.mimeTypes.length;
  guid += nav.userAgent.replace(/\D+/g, '');
  guid += nav.plugins.length;
  guid += screen.height || '';
  guid += screen.width || '';
  guid += screen.pixelDepth || '';

  return md5(guid);
} // get_agent_hash
//----------------------------------------------------
function get_protection_hash()
{
  hash = "";
  
  if(localStorage) hash = localStorage.getItem('protection_hash');
  
  if(hash == "") hash = protection_hash;
  
  return hash;
} // get_protection_hash
//----------------------------------------------------
function set_protection_hash(hash)
{ 
  protection_hash = hash;
  
  if(localStorage) localStorage.setItem('protection_hash', hash);
} // set_protection_hash
//----------------------------------------------------
function check_actual_hash(url)
{
  url.href = url.href.replace(/&hash=[^&]+/, "&hash=" + get_protection_hash());
} // check_actual_hash
//----------------------------------------------------
function refresh_captcha()
{
  var elm = document.getElementById('captcha_picture');
  if(elm)
  {
    elm.src = 'captcha/captcha.php?d=' + new Date().getTime();
  }
}
//----------------------------------------------------
function show_hide_captcha(state)
{
  var elms = document.getElementsByClassName("captcha_area");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].style.display = state ? "table-row" : "none";
  }

  if(!state) return;

  var elm = document.getElementById('captcha_picture');
  if(elm)
  {
    elm.src = 'captcha/captcha.php?d=' + new Date().getTime();
  }
}
//----------------------------------------------------
function focus_field(tfid)
{
  var elm = document.getElementById(tfid);
  if(!elm) return;

  elm.focus();
}
//----------------------------------------------------
function select_text_in_field(tfid)
{
  var elm = document.getElementById(tfid);
  if(!elm) return;

  elm.select();
  
  document.execCommand("copy");
}
//----------------------------------------------------
// moves selected items from one list to another
//----------------------------------------------------
Forum.moveSelectedItems = function(source_list, target_list)
{
  var i;
  var existing_items = {};

  for(i = target_list.options.length - 1; i >= 0 ; i--)
  {
    existing_items['item' + target_list.options[i].value] = 1;
  }

  for(i = 0; i < source_list.length; i++)
  {
    if(source_list.options[i].selected && source_list.options[i].value != '#')
    {
      if(!existing_items['item' + source_list.options[i].value])
      {
        var option = new Option(source_list.options[i].text,
                                source_list.options[i].value,
                                false, true
                               );
        target_list.options[target_list.options.length] = option;
      }
      else
      {
        Forum.selectValue(target_list, source_list.options[i].value);
      }
    }
  }

  for(i = source_list.options.length - 1; i >= 0 ; i--)
  {
    if(source_list.options[i].selected && source_list.options[i].value != '#')
    {
      source_list.options[i] = null;
    }
  }
  
  Forum.fireEvent(source_list, 'change');
  Forum.fireEvent(target_list, 'change');
}; // moveSelectedItems
//----------------------------------------------------
Forum.removeAllItems = function(list)
{
  for(i = list.options.length - 1; i >= 0 ; i--)
  {
    if(list.options[i].selected)
    {
      list.options[i] = null;
    }
  }
  
  Forum.fireEvent(list, 'change');
}; // removeAllItems
//----------------------------------------------------
Forum.selectAll = function(list)
{
  var changed = false;
  
  for(var i = 0; i < list.options.length; i++)
  {
    if(!list.options[i].selected)
    {
      list.options[i].selected = true;
      changed = true;
    }
  }
  
  if(changed) Forum.fireEvent(list, 'change');
}; // selectAll
//----------------------------------------------------
Forum.unselectAll = function(list)
{
  var changed = false;

  for(var i = 0; i < list.options.length; i++)
  {
    if(list.options[i].selected) 
    {
      list.options[i].selected = false;
      changed = true;
    }
  }
  
  if(changed) Forum.fireEvent(list, 'change');
}; // unselectAll
//----------------------------------------------------
Forum.selectValue = function(list, value)
{
  var changed = false;

  list.options.selectedIndex = 0;

  for(var i = 0; i < list.length; i++)
  {
    var new_state = (list.options[i].value == value);
    
    if(list.options[i].selected != new_state)
    {
      list.options[i].selected = new_state;
      changed = true;
    }
  }
  
  if(changed) Forum.fireEvent(list, 'change');
}; // selectValue
//----------------------------------------------------
Forum.selectedText = function(list)
{
  for(var i = 0; i < list.length; i++)
  {
    if(list.options[i].selected) return list.options[i].text;
  }

  return "";
}; // selectedText
//----------------------------------------------------
Forum.nl2br = function(str)
{
   return str.replace(/\n/g, "<br>");
}; // nl2br
//----------------------------------------------------
Forum.isEmptyObject = function(obj)
{
  for(var prop in obj)
  {
    if(Object.prototype.hasOwnProperty.call(obj, prop))
    {
      return false;
    }
  }

  return true;
}; // isEmptyObject
//----------------------------------------------------
Forum.objectPropertiesCount = function(obj)
{
  var count = 0;

  for(var prop in obj)
  {
    if(Object.prototype.hasOwnProperty.call(obj, prop))
    {
      count++;
    }
  }

  return count;
}; // objectPropertiesCount
//----------------------------------------------------
Forum.showDirty = function(formObj)
{
  var dirties = "";
  
  for(var i = 0; i < formObj.elements.length; i++)
  {
    var element = formObj.elements[i];
    var type = element.type;

    if(type == "checkbox" || type == "radio")
    {
      if(element.checked != element.defaultChecked)
      {
        dirties += element.name + ": " + (element.checked ? 'checked' : 'unchecked') + ', default: ' + (element.checked ? 'defaultChecked' : 'defaultChecked');
      }
    }
    else if (type == "hidden" || type == "password" || type == "text" ||
            type == "textarea")
    {
      if(element.value != element.defaultValue)
      {
        dirties += element.name + ":\n" + element.value + "\n\ndefault:\n" + element.defaultValue;
      }
    }
    else if (type == "select-one" || type == "select-multiple")
    {
      var selection = "";
      var defaultSelection = "";
      
      for (var j = 0; j < element.options.length; j++)
      {
        if(element.options[j].selected) selection += element.options[j].value;
        if(element.options[j].defaultSelected) defaultSelection += element.options[j].value;
      }
      
      if(selection != defaultSelection)
      {
        dirties += element.name + ":\n" + selection + "\n\ndefault:\n" + defaultSelection;
      }
    }
  }
}; // showDirty
//----------------------------------------------------
Forum.formDirty = function(formObj)
{
  for(var i = 0; i < formObj.elements.length; i++)
  {
    var element = formObj.elements[i];
    var type = element.type;

    if(type == "checkbox" || type == "radio")
    {
      if(element.checked != element.defaultChecked)
      {
        return true;
      }
    }
    else if(type == "file")
    {
      if(element.value != "")
      {
        return true;
      }
    }
    else if (type == "hidden" || type == "password" || type == "text" ||
            type == "textarea")
    {
      if(element.value != element.defaultValue)
      {
        return true;
      }
    }
    else if (type == "select-one" || type == "select-multiple")
    {
      for (var j = 0; j < element.options.length; j++)
      {
        if(element.options[j].selected !=
           element.options[j].defaultSelected)
        {
          return true;
        }
      }
    }
  }

  return false;
}; // formDirty
//----------------------------------------------------
Forum.clearForm = function(formObj)
{
  for(var i = 0; i < formObj.elements.length; i++)
  {
    var element = formObj.elements[i];
    var type = element.type;

    if(type == "checkbox" || type == "radio")
    {
      element.checked = false;
    }
    else if (type == "password" || type == "text" || type == "textarea" || type == "hidden")
    {
      element.value = "";
    }
    else if (type == "select-one" || type == "select-multiple")
    {
      for (var j = 0; j < element.options.length; j++)
      {
        element.options[j].selected = false;
      }
    }
  }

  return false;
}; // clearForm
//----------------------------------------------------
Forum.getClientDimensions = function()
{
  var dimensions = {};

  if(window.innerWidth)
  {
    dimensions.height = window.innerHeight;
    dimensions.width = window.innerWidth;
  }
  else if(document.documentElement)
  {
    dimensions.height = document.documentElement.clientHeight;
    dimensions.width = document.documentElement.clientWidth;
  }
  else
  {
    dimensions.height = document.body.clientHeight;
    dimensions.width = document.body.clientWidth;
  }

  return dimensions;
}; // getClientDimenstions
//----------------------------------------------------
Forum.reload_captcha = function(picture_id, session_var, elm_id)
{
  var elm = document.getElementById(picture_id);
  if(!elm) return;

  elm.src = "captcha/captcha.php?rnd=" + Math.round(Math.random()*1000) + "&force_new=1&session_var=" + session_var;

  elm = document.getElementById(elm_id);
  if(!elm) return;

  elm.value = "";
  elm.focus();
} // reload_captcha
//----------------------------------------------------
Forum.decode_html = function(html)
{
  var txt = document.createElement("textarea");
  txt.innerHTML = html;
  return txt.value;
} // decode_html
//----------------------------------------------------
Forum.prepare_message = function(str)
{
  var output = "";
  var start = 0;
  
  var regexp = new RegExp("\\[html\\]([\\s\\S]+?)\\[\\/html\\]", "mgi");
  while (result = regexp.exec(str)) {
    output += str.substring(start, result.index).replace(/&/g, "&").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
    output += result[1];
    
    start = regexp.lastIndex;
  }
  
  output += str.substring(start, str.length).replace(/&/g, "&").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
  
  return output;
}; // prepare_message
//----------------------------------------------------
Forum.escape_html = function(str)
{
  return str.replace(/&/g, "&").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}; // escape_html
//----------------------------------------------------
Forum.show_profiling_info = function() 
{
  var elm = document.getElementById("profiling_info");
  if(!elm) return;
  
  elm.style.display = "block";
}; // show_profiling_info
//----------------------------------------------------
Forum.show_sys_bubblebox = function(title_text, message_text, autohide)
{
  var msgbox = document.getElementById("sys_bubblebox");
  if(!msgbox) return;

  msgbox.style.display = "block";

  var title = document.getElementById("sys_bubblebox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var txt = document.getElementById("sys_bubblebox_msg");
  if(txt) txt.innerHTML = Forum.prepare_message(message_text);

  if(autohide) msgbox.timeout = setTimeout(Forum.hide_sys_bubblebox, 1300);
} // show_sys_bubblebox
//----------------------------------------------------
Forum.show_user_msgbox = function(title_text, message_text, icon, buttons, autohide, on_hide)
{
  var msgbox = document.getElementById("user_msgbox");
  if(!msgbox) return;

  if(on_hide) msgbox.on_hide = on_hide;
  else        msgbox.on_hide = null;

  var title = document.getElementById("user_msgbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var txt = document.getElementById("user_msgbox_text");
  if(txt) txt.innerHTML = Forum.prepare_message(message_text);

  var mbox_icon = document.getElementById("user_msgbox_icon");
  if(mbox_icon) mbox_icon.innerHTML = "<img src='" + VIEW_PATH + "images/" + icon + " '/>";

  var button_to_focus = null;

  var mbox_buttons = document.getElementById("user_msgbox_buttons");
  if(mbox_buttons)
  {
    var elm;
    while(elm = mbox_buttons.lastChild) mbox_buttons.removeChild(elm);

    if(buttons)
    {
      for(var i = 0; i < buttons.length; i++)
      {
        elm = document.createElement("input");
        elm.type = "button";
        elm.value = buttons[i].caption;
        elm.className = "standard_button";
        if(buttons[i].addClass) elm.className += " " + buttons[i].addClass;
        if(buttons[i].handler)
        {
          elm.onclick = buttons[i].handler;
        }

        if(i == 0) button_to_focus = elm;

        mbox_buttons.appendChild(elm);
      }
    }
  }

  msgbox.style.display = "table";

  if(button_to_focus)
  {
    setTimeout(function() { button_to_focus.focus(); }, 100);
  }

  if(autohide) msgbox.timeout = setTimeout(Forum.hide_user_msgbox, 1300);
}; // show_user_msgbox
//----------------------------------------------------
Forum.hide_user_msgbox = function(suppress_on_hide)
{
  var msgbox = document.getElementById("user_msgbox");
  if(!msgbox) return false;

  var was_open = (msgbox.style.display == "table");

  msgbox.style.display = "none";

  if(msgbox.timeout)
  {
    clearTimeout(msgbox.timeout);
    msgbox.timeout = null;
  }

  if(!suppress_on_hide && msgbox.on_hide)
  {
    msgbox.on_hide();
  }

  return was_open;
}; // hide_user_msgbox
// --------------------------------------------------------
Forum.show_user_textarea = function(title_text, field_caption, default_value, icon, buttons, height)
{
  var msgbox = document.getElementById("user_msgbox");
  if(!msgbox) return;

  msgbox.on_hide = null;
  
  var title = document.getElementById("user_msgbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var mbox_icon = document.getElementById("user_msgbox_icon");
  
  if(mbox_icon) 
  {
    if(icon != '') mbox_icon.innerHTML = "<img src='" + VIEW_PATH + "images/" + icon + " '/>";
    else           mbox_icon.innerHTML = "";
  }    

  var txt = document.getElementById("user_msgbox_text");
  
  if(field_caption != '') field_caption += '<br>';
  if(txt) txt.innerHTML = '<div class="_sys_user_input_wrapper">' + field_caption + '<textarea id="sys_user_textarea" name="sys_user_textarea" class="_sys_user_textarea" style="height:' + height + 'px">' + Forum.escape_html(default_value) + '</textarea></div>';

  var sys_user_textarea = document.getElementById("sys_user_textarea");

  var mbox_buttons = document.getElementById("user_msgbox_buttons");
  if(mbox_buttons)
  {
    var elm;
    while(elm = mbox_buttons.lastChild) mbox_buttons.removeChild(elm);

    if(buttons)
    {
      for(var i = 0; i < buttons.length; i++)
      {
        elm = document.createElement("input");
        elm.type = "button";
        elm.value = buttons[i].caption;
        elm.className = "standard_button";
        if(buttons[i].addClass) elm.className += " " + buttons[i].addClass;
        if(buttons[i].handler)
        {
          elm.onclick = buttons[i].handler;
          
          // on ctrl + enter for the first button
          if(i == 0)
          {
            sys_user_textarea.onkeypress = function (ev)
            {
              if(ev.ctrlKey && (ev.keyCode == 13 || ev.keyCode == 10))
              {
                buttons[0].handler();
              }
            }
          }
        }

        mbox_buttons.appendChild(elm);
      }
    }
  }

  msgbox.style.display = "table";

  if(sys_user_textarea)
  {
    setTimeout(function() { 
      sys_user_textarea.focus(); 

      if(sys_user_textarea.value.length > 0)
        sys_user_textarea.setSelectionRange(sys_user_textarea.value.length, sys_user_textarea.value.length);
    }, 100);
  }
}; // show_user_textarea
//----------------------------------------------------
Forum.show_user_inputbox = function(title_text, field_caption, default_value, icon, buttons)
{
  var msgbox = document.getElementById("user_msgbox");
  if(!msgbox) return;

  msgbox.on_hide = null;
  
  var title = document.getElementById("user_msgbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var mbox_icon = document.getElementById("user_msgbox_icon");
  
  if(mbox_icon) 
  {
    if(icon != '') mbox_icon.innerHTML = "<img src='" + VIEW_PATH + "images/" + icon + " '/>";
    else           mbox_icon.innerHTML = "";
  }    

  var txt = document.getElementById("user_msgbox_text");
  if(txt) txt.innerHTML = '<div class="_sys_user_input_wrapper">' + field_caption + ':<br><input type="text" id="sys_user_input" name="sys_user_input" class="_sys_user_input" value="' + Forum.escape_html(default_value) + '"></div>';

  var sys_user_input = document.getElementById("sys_user_input");

  var mbox_buttons = document.getElementById("user_msgbox_buttons");
  if(mbox_buttons)
  {
    var elm;
    while(elm = mbox_buttons.lastChild) mbox_buttons.removeChild(elm);

    if(buttons)
    {
      for(var i = 0; i < buttons.length; i++)
      {
        elm = document.createElement("input");
        elm.type = "button";
        elm.value = buttons[i].caption;
        elm.className = "standard_button";
        if(buttons[i].addClass) elm.className += " " + buttons[i].addClass;
        if(buttons[i].handler)
        {
          elm.onclick = buttons[i].handler;
          
          // on enter for the first button
          if(i == 0)
          {
            sys_user_input.onkeypress = function (ev)
            {
              if(ev.keyCode == 13 || ev.keyCode == 10)
              {
                buttons[0].handler();
              }
            }
          }
        }

        mbox_buttons.appendChild(elm);
      }
    }
  }

  msgbox.style.display = "table";

  if(sys_user_input)
  {
    setTimeout(function() { 
      sys_user_input.focus(); 
      sys_user_input.select();
    }, 100);
  }
}; // show_user_inputbox
//----------------------------------------------------
var zoom_preview_factor = 1;
// --------------------------------------------------------
Forum.scale_preview_image = function(img)
{
  img.style.width = 'auto';
  img.style.height = 'auto';

  var img_width = img.naturalWidth;
  var img_height = img.naturalHeight;

  img_width *= zoom_preview_factor;
  img_height *= zoom_preview_factor;

  var dims = Forum.getClientDimensions();

  var max_width = dims.width - 80;
  var max_height = dims.height - 100;
  
  if(pin_the_menu)
  {
    var float_header_container = document.getElementById("float_header_container");
    if(float_header_container)
    {
      var rect = float_header_container.getBoundingClientRect();
      max_height = Math.round(dims.height - 3*rect.height);
    }
  }

  var width_proportion = max_width / img_width;
  var height_proportion = max_height / img_height;
  
  var proportion = Math.min(max_width / img_width, max_height / img_height);
  
  if(proportion >= 1) return;
  
  img_width = Math.round(img_width * proportion);
  img_height = Math.round(img_height * proportion);

  img.style.width = img_width + 'px';
  img.style.height = img_height + 'px';
} // scale_preview_image
// --------------------------------------------------------
Forum.slide_image_preview = function(event, direction)
{
  var preview = document.getElementById("sys_image_preview");
  if(!preview || preview.style.display != "table" || preview.total_count < 2) return;

  var elm = document.getElementById("preview_navigation_status");
  if(!elm) return;

  if(event.preventDefault)
    event.preventDefault();
  else
    event.returnValue = false;

  if(event.stopPropagation)
    event.stopPropagation();
  
  if(direction == "previous") 
  {
    Forum.swap_preview_image(elm.previous_link);
  }
  else if(direction == "next") 
  {
    Forum.swap_preview_image(elm.next_link); 
  }
  
  return false;
} // Forum.slide_image_preview
// --------------------------------------------------------
Forum.swap_preview_image = function(img_link)
{
  var lbox = document.getElementById("sys_image_preview");
  var img = document.getElementById("sys_preview_image");

  if(!lbox || !img) return;
  
  var elm = document.getElementById("preview_navigation_status");
  if(elm)
  {
    elm.previous_link = img_link.previous_link;
    elm.next_link = img_link.next_link;
    
    elm.innerHTML = img_link.position + " / " + elm.total_count;
  }
  
  elm = document.getElementById("sys_image_preview_open_new");
  if(elm) 
  {
    elm.href = img_link.getAttribute('href');
    
    if(elm.href.indexOf("?") == -1) elm.href += "?inline=1";
    else                            elm.href += "&inline=1";
  }

  img.parentNode.classList.add("loading_in_progress");

  if(lbox.classList) lbox.classList.remove('_sys_image_preview_stop_animation');
  
  setTimeout(async function() {
    await fetch(img_link.getAttribute('href'), {cache: 'no-cache'});

    img.src = img_link.getAttribute('href');
  }, 300);
} // swap_preview_image
// --------------------------------------------------------
Forum.show_image_preview = function(img_link)
{
  var parent_post = null;
  var current_parent = img_link.parentNode;
  while(current_parent)
  {
    if(current_parent.classList && current_parent.classList.contains("message_text"))  
    {
      parent_post = current_parent;
      break;
    }
    
    current_parent = current_parent.parentNode;
  }  
  
  var elm = null;
  var preview = document.getElementById("sys_image_preview");
  var img = document.getElementById("sys_preview_image");
  if(!img) return;
  
  if(parent_post)
  {
    var preview_links = parent_post.querySelectorAll('.lightbox_image');
    var cnt = preview_links.length;
    
    var first_link = null;
    var previous_link = null;
    var total_count = 0;
    for(var i = 0; i < cnt; i++)
    {
      preview_links[i].position = ++total_count;
      
      if(!first_link) first_link = preview_links[i];
      
      if(previous_link) 
      {
        preview_links[i].previous_link = previous_link;
        previous_link.next_link = preview_links[i];
      }
      
      previous_link = preview_links[i];
    }
    
    if(previous_link)
    {
      previous_link.next_link = first_link;
      first_link.previous_link = previous_link;
    }
    
    preview.total_count = total_count;
    
    elm = document.getElementById("preview_navigation");
    if(elm)
    {
      elm.total_count = total_count;
    }

    if(total_count > 1)
    {
      if(img && !img.event_added)
      {
        img.event_added = 1;
        
        Forum.addXEvent(img, 'swiped-up', function(event) { 
          var preview = document.getElementById("sys_image_preview");
          if(!preview || preview.style.display != "table") return;
          
          Forum.hide_image_preview();
        });

        Forum.addXEvent(img, 'swiped-down', function(event) { 
          var preview = document.getElementById("sys_image_preview");
          if(!preview || preview.style.display != "table") return;

          Forum.hide_image_preview();
        });
        
        Forum.addXEvent(img, 'swiped-left', function(event) { 
          Forum.slide_image_preview(event, "next"); 
        });

        Forum.addXEvent(img, 'swiped-right', function(event) { 
          Forum.slide_image_preview(event, "previous"); 
        });
      }  
        
      elm = document.getElementById("preview_navigation_status");
      if(elm)
      {
        elm.total_count = total_count;
        
        elm.previous_link = img_link.previous_link;
        elm.next_link = img_link.next_link;
        
        elm.innerHTML = img_link.position + " / " + elm.total_count;
      }
      
      elm = document.getElementById("preview_navigation_previous");
      if(elm && !elm.event_added)
      {
        elm.event_added = 1;
        
        Forum.addXEvent(elm, 'click', function(event) { 
          Forum.slide_image_preview(event, "previous"); 
        });

        Forum.addXEvent(document.body, 'keydown', function (event) { 
          event = event || window.event;
          var keyCode = event.keyCode || event.which;
          
          if(keyCode == 37 || keyCode == 38)
          {
            Forum.slide_image_preview(event, "previous");
          }

          if(keyCode == 39 || keyCode == 40)
          {
            Forum.slide_image_preview(event, "next");
          }

          if(keyCode == 32)
          {
            Forum.slide_image_preview(event, "next");
          }
        });
      }

      elm = document.getElementById("preview_navigation_next");
      if(elm && !elm.event_added)
      {
        elm.event_added = 1;
        
        Forum.addXEvent(elm, 'click', function(event) { 
          Forum.slide_image_preview(event, "next"); 
        });
      }
    }
  }
  
  if(!window.history.state.is_active || window.history.state.is_active == 2)
  {
    debug_line('Img Preview - the current work_stage slot is not in use or the same usage, reusing it', 'history');
    window.history.replaceState({ work_stage: 1, is_active: 2 }, null, get_history_url());

    // we replace the default history undo action with this action
    debug_line('Previous action removed from the stack', 'history');
    history_undo_actions_stack.pop();
  }
  else
  {
    debug_line('Img Preview - the current work_stage slot is already in use, adding new one', 'history');
    window.history.pushState({ work_stage: 1, is_active: 2 }, null, get_history_url());
  }

  debug_line('ImgPreview action put to the stack', 'history');
  history_undo_actions_stack.push(function () {
    var lbox = document.getElementById("sys_image_preview");
    if(lbox)
    {
      lbox.style.display = "none";
    }
    
    // cut off the forward history
    debug_line('Img Preview - cutting forward history off', 'history');
    window.history.pushState({ work_stage: 1, is_active: 0 }, null, get_history_url());

    debug_line('Go-back action put to the stack', 'history');
    history_undo_actions_stack.push(function () {
      debug_line('Doing back', 'history');
      window.history.back();
    });
  });
  
  var lbox = document.getElementById("sys_image_preview");
  var img_close = document.getElementById("sys_image_preview_close");
  var img_open_new = document.getElementById("sys_image_preview_open_new");

  if(!lbox) return;

  if(lbox.classList) lbox.classList.remove('_sys_image_preview_stop_animation');
  if(img.classList) img.classList.remove('_sys_lightbox_image_reveal');
  if(img_close.classList) img_close.classList.remove('_sys_lightbox_image_reveal');
  if(img_open_new && img_open_new.classList) img_open_new.classList.remove('_sys_lightbox_image_reveal');

  img.parentNode.classList.add("first_loading_in_progress");
  
  img.style.display = 'none';
  img.style.width = 'auto';
  img.style.height = 'auto';
  lbox.style.display = 'table';
  
  if(img_open_new) 
  {
    img_open_new.href = img_link.getAttribute('href');
    
    if(img_open_new.href.indexOf("?") == -1) img_open_new.href += "?inline=1";
    else                                     img_open_new.href += "&inline=1";
  }

  img.onerror = function(e)
  {
    img.parentNode.classList.remove("first_loading_in_progress");

    img.src = VIEW_PATH + 'images/noimage_big.png';
  }

  img.onload = function(e)
  {
    Forum.scale_preview_image(img);

    img.parentNode.classList.remove("first_loading_in_progress");
    img.parentNode.classList.remove("loading_in_progress");
    img.style.display = 'block';

    setTimeout(function() {
      if(img.classList) 
      {
        img.classList.add('_sys_lightbox_image_reveal');
      }

      if(lbox.classList) lbox.classList.add('_sys_image_preview_stop_animation');
      if(img_close.classList) img_close.classList.add('_sys_lightbox_image_reveal');
      if(img_open_new && img_open_new.classList) img_open_new.classList.add('_sys_lightbox_image_reveal');

      elm = document.getElementById("preview_navigation");
      if(elm && elm.total_count > 1 && preview.style.display == "table")
      {
        elm.style.display = "block";
      }
    }, 300);
  }

  setTimeout(async function() {
    await fetch(img_link.getAttribute('href'), {cache: 'no-cache', mode: 'no-cors'});
	
    img.src = img_link.getAttribute('href');
  }, 300);
} // show_image_preview
// --------------------------------------------------------
Forum.hide_image_preview = function()
{
  debug_line('doing hide', 'history');
  
  var elm = document.getElementById("sys_image_preview");
  if(!elm) return;

  var was_open = (elm.style.display == "table");

  elm.style.display = "none";
  
  elm = document.getElementById("preview_navigation");
  if(elm)
  {
    elm.style.display = "none";
  }
  
  if (was_open)
  {
    window.history.back();
  }
  
  return was_open;
}; // hide_image_preview
// --------------------------------------------------------
Forum.show_post_preview = function(title_text, html, buttons)
{
  var lbox = document.getElementById("sys_lightbox");
  if(!lbox) return;

  var title = document.getElementById("sys_lightbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var head = document.getElementById("sys_lightbox_head");
  var body = document.getElementById("sys_lightbox_body");
  var toolbar = document.getElementById("sys_lightbox_toolbar");

  Forum.last_element_parent = null;

  if(body && head && toolbar)
  {
    var dims = Forum.getClientDimensions();

    var width = Math.round(0.8*dims.width);
    var height = Math.round(0.7*dims.height);

    head.style.width = width + "px";
    body.style.width = width + "px";
    toolbar.style.width = width + "px";

    body.style.height = height + "px";

    toolbar.style.display = "block";

    var html_container = document.createElement("div");
    html_container.className = "post_preview_container";
    html_container.innerHTML = html;
    body.appendChild(html_container);

    var codes = document.body.getElementsByTagName("code");
    for(var i = 0; i < codes.length; i++)
    {
      hljs.highlightBlock(codes[i]);
    }

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

  hljs.initHighlighting();
  
  init_lightbox_images();
  init_embedded_widgets();

  // by using insertAdjacentHTML for a content with images
  // they are loaded not immediately, we need a timeout before
  // calcualtion of the heights
  setTimeout(init_more_buttons, 1000);
  setTimeout(init_more_buttons, 2500);
} // show_post_preview
// --------------------------------------------------------
Forum.show_attachment_gallery = function(title_text, buttons)
{
  var lbox = document.getElementById("sys_lightbox");
  if(!lbox) return;

  var title = document.getElementById("sys_lightbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var head = document.getElementById("sys_lightbox_head");
  var body = document.getElementById("sys_lightbox_body");
  var toolbar = document.getElementById("sys_lightbox_toolbar");

  Forum.last_element_parent = null;

  if(body && head && toolbar)
  {
    var dims = Forum.getClientDimensions();

    var width = Math.round(dims.width*0.95);
    var height = Math.round(dims.height*0.75);
    
    head.style.width = width + "px";
    body.style.width = width + "px";
    toolbar.style.width = width + "px";

    body.style.height = height + "px";

    var attachment_gallery_area = document.createElement("div");
    attachment_gallery_area.classList.add("attachment_gallery_area");
    body.appendChild(attachment_gallery_area);
    
    var computedStyle = getComputedStyle(attachment_gallery_area);
    height -= parseInt(computedStyle['padding-top'], 10);
    
    attachment_gallery_area.style.height = height + "px";

    toolbar.style.display = "block";
    
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
}
// --------------------------------------------------------
Forum.show_topic_selector = function(title_text, buttons, show_new_topic, merge_modus, width)
{
  var lbox = document.getElementById("sys_lightbox");
  if(!lbox) return;

  var title = document.getElementById("sys_lightbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var head = document.getElementById("sys_lightbox_head");
  var body = document.getElementById("sys_lightbox_body");
  var toolbar = document.getElementById("sys_lightbox_toolbar");
  var found_topics = document.getElementById("found_topics");
  var elm = document.getElementById("search_topic_area");

  var form = document.getElementById('topic_search_form');
  if(form) form.elements['merge_modus'].value = merge_modus ? 1 : 0;

  Forum.last_element_parent = null;

  if(body && head && toolbar && found_topics && elm)
  {
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

    // remove old entries
    for(var i = found_topics.length - 1; i >= 0 ; i--)
    {
      found_topics.options[i] = null;
    }

    elm = document.getElementById("new_topic_block");
    if(elm) elm.style.display = show_new_topic ? "table" : "none";
  }

  lbox.style.display = "table";

  Forum.fireEvent(found_topics, 'show');
  Forum.fireEvent(found_topics, 'change');
  
  elm = document.getElementById("topic_to_search");
  if(elm)
  {
    elm.value = "";
    elm.focus();
  }
} // show_topic_selector
// --------------------------------------------------------
Forum.update_user_tags = function(new_user_tags)
{
  if(typeof user_tags == 'undefined') return;

  var tag_list = document.getElementById("tag_list");
  if(!tag_list) return;
  
  user_tags = new_user_tags;

  // remove old entries
  for(var i = tag_list.length - 1; i >= 0 ; i--)
  {
    tag_list.options[i] = null;
  }
  
  for(tgid in user_tags)
  {
    var option = new Option(user_tags[tgid],
                            tgid,
                            false, false
                           );
    tag_list.options[tag_list.options.length] = option;
  }
  
  Forum.fireEvent(tag_list, 'change');
  
  var selected_tags;
  var elms = document.getElementsByClassName("manage_tags_list");
  for(var i = 0; i < elms.length; i++)
  {
    selected_tags = elms[i].getAttribute("data-selected-tags");
    selected_tags = selected_tags.split(",");
    
    rebuild_selected_tag_list(elms[i].getAttribute('data-pid'), selected_tags);
  }
} // update_user_tags
// --------------------------------------------------------
Forum.show_tag_editor = function(title_text, buttons, width)
{
  var lbox = document.getElementById("sys_lightbox");
  if(!lbox) return;

  var title = document.getElementById("sys_lightbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var head = document.getElementById("sys_lightbox_head");
  var body = document.getElementById("sys_lightbox_body");
  var toolbar = document.getElementById("sys_lightbox_toolbar");
  
  var tag_list = document.getElementById("tag_list");
  var elm = document.getElementById("tag_editor");

  Forum.last_element_parent = null;

  if(body && head && toolbar && tag_list && elm)
  {
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

    // remove old entries
    for(var i = tag_list.length - 1; i >= 0 ; i--)
    {
      tag_list.options[i] = null;
    }
    
    if(typeof user_tags != 'undefined')
    for(tgid in user_tags)
    {
      var option = new Option(user_tags[tgid],
                              tgid,
                              false, false
                             );
      tag_list.options[tag_list.options.length] = option;
    }
  }

  lbox.style.display = "table";

  Forum.fireEvent(tag_list, 'show');
  Forum.fireEvent(tag_list, 'change');
} // show_tag_editor
// --------------------------------------------------------
Forum.show_notes_editor = function(title, elm_id, uid, height)
{
  var elm = document.getElementById(elm_id);
  if(!elm) return;

  var mbuttons = [
    {
      caption: msg_Save,
      handler: function() {
        var ta = document.getElementById("sys_user_textarea");
        if(ta) 
          save_notes(uid, elm_id, ta.value);
        else
          Forum.hide_user_msgbox();
      }
    },
    {
      caption: msg_Cancel,
      handler: function() {
        Forum.hide_user_msgbox();
      }
    }
  ];

  Forum.show_user_textarea(title, '', elm.getAttribute('data-notes'), 'icon-edit.png', mbuttons, height);

  return false;
} // show_notes_editor
// --------------------------------------------------------
function send_empty_hash_report(report_id)
{
  var ajax = new Forum.AJAX();
  ajax.timeout = 10000; // 10 seconds

  ajax.setGET('report_id', report_id);
  ajax.setGET('user_name', user_name);
  ajax.setGET('session_id', session_id);
  ajax.setGET('session_start_time', session_start_time);
  ajax.setGET('session_cookie', session_cookie);
  ajax.setGET('previous_session_id', previous_session_id);
  ajax.setGET('previous_session_start_time', previous_session_start_time);
  ajax.setGET('previous_session_cookie', previous_session_cookie);
  ajax.setGET('hash', get_protection_hash());

  ajax.request("ajax/send_empty_hash_report.php");
} // send_empty_hash_report
// --------------------------------------------------------
var save_notes_ajax = null;

function save_notes(uid, elm_id, notes)
{
  Forum.show_sys_progress_indicator(true);

  if(!save_notes_ajax)
  {
    save_notes_ajax = new Forum.AJAX();

    save_notes_ajax.timeout = TIMEOUT;

    save_notes_ajax.beforestart = function() { break_check_new_messages(); };
    save_notes_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    save_notes_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          var elm = document.getElementById(elm_id);
          if(elm)
          {
            elm.setAttribute('data-notes', this.notes);
            elm.innerHTML = response.notes;
          }
        }

        Forum.show_sys_progress_indicator(false);
        Forum.hide_user_msgbox(); 
      }
      catch(err)
      {
        Forum.show_sys_progress_indicator(false);
        Forum.hide_user_msgbox(); 
        
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }
    };

    save_notes_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);
      Forum.hide_user_msgbox(); 

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  save_notes_ajax.abort();
  save_notes_ajax.resetParams();

  save_notes_ajax.setPOST('save_notes', "1");
  save_notes_ajax.setPOST('hash', get_protection_hash());
  save_notes_ajax.setPOST('user_logged', user_logged);
  save_notes_ajax.setPOST('trace_sql', trace_sql);
  save_notes_ajax.setPOST('uid', uid);
  save_notes_ajax.setPOST('notes', notes);

  save_notes_ajax.notes = notes;

  save_notes_ajax.request("ajax/process.php");

  return false;
} // save_notes
// --------------------------------------------------------
Forum.show_post_comment = function(title_text, buttons, width)
{
  var lbox = document.getElementById("sys_lightbox");
  if(!lbox) return;

  var title = document.getElementById("sys_lightbox_title");
  if(title) title.innerHTML = Forum.escape_html(title_text);

  var head = document.getElementById("sys_lightbox_head");
  var body = document.getElementById("sys_lightbox_body");
  var toolbar = document.getElementById("sys_lightbox_toolbar");
  var elm = document.getElementById("post_comment_area");

  Forum.last_element_parent = null;

  if(body && head && toolbar && elm)
  {
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

    elm = document.getElementById("post_comment");
    if(elm) elm.value = "";
  }

  lbox.style.display = "table";

  elm = document.getElementById("post_comment");
  if(elm) elm.focus();
} // show_post_comment
// --------------------------------------------------------
Forum.hide_sys_bubblebox = function()
{
  var msgbox = document.getElementById("sys_bubblebox");
  if(!msgbox) return;

  msgbox.style.display = "none";

  if(msgbox.timeout)
  {
    clearTimeout(msgbox.timeout);
    msgbox.timeout = null;
  }
} // hide_sys_bubblebox
// --------------------------------------------------------
Forum.hide_sys_lightbox = function()
{
  var lbox = document.getElementById("sys_lightbox");
  if(!lbox) return;

  lbox.style.display = "none";

  var body = document.getElementById("sys_lightbox_body");
  var head = document.getElementById("sys_lightbox_head");
  var toolbar = document.getElementById("sys_lightbox_toolbar");

  if(!body || !head || !toolbar) return;

  // let the user to handle closing and do any desired additional actions
  if(typeof Forum.on_lightbox_close == "function")
  {
    Forum.on_lightbox_close();
    Forum.on_lightbox_close = null;
  }

  if(body.classList)
  {
    body.classList.remove('_sys_lightbox_loading');
    body.classList.remove('_sys_lightbox_body_center');
  }

  head.style.width = "";
  body.style.width = "";
  toolbar.style.width = "";
  toolbar.style.display = "none";

  body.style.height = "";

  if(body.lastChild)
  {
    if(Forum.last_element_parent)
    {
      body.lastChild.style.display = "none";
      Forum.last_element_parent.appendChild(body.lastChild);
      Forum.last_element_parent = null;
    }
    else
    {
      body.removeChild(body.lastChild);
    }
  }

  var elm;
  while(elm = toolbar.lastChild) toolbar.removeChild(elm);
} // hide_sys_lightbox
// --------------------------------------------------------
Forum.handle_sys_msgbox_esc = function(ev)
{
  if(ev.keyCode == 27) // Esc
  {
    // user message box was open. Close only this upon escape.
    if(!Forum.hide_user_msgbox() && !Forum.hide_image_preview())
    {
      Forum.hide_sys_lightbox();
      Forum.hide_sys_bubblebox();
    }

    if(typeof user_esc_handler == 'function') user_esc_handler();
    
    var elms = document.getElementsByClassName("profiling_info");
    for(var i = 0; i < elms.length; i++)
    {
      elms[i].style.display = "none";
    }
    
    elms = document.getElementsByClassName("page_jumper");
    for(var i = 0; i < elms.length; i++)
    {
      elms[i].style.display = "none";
    }
    
    show_debug_console(false);
  }
}; // handle_sys_msgbox_esc
// --------------------------------------------------------
Forum.add_sys_message_handler = function()
{
  if(navigator.userAgent.toLowerCase().indexOf("msie") != -1)
  {
    Forum.addXEvent(document.body, 'keydown', Forum.handle_sys_msgbox_esc);
  }
  else
  {
    Forum.addXEvent(window, 'keydown', Forum.handle_sys_msgbox_esc);
  }
}; // add_sys_message_handler
// --------------------------------------------------------
Forum.show_sys_progress_indicator = function(state)
{
  var msg = document.getElementById("sys_progress_indicator");
  if(!msg) return;

  if(!state)
  {
    msg.style.display = "none";
    return;
  }

  msg.style.display = "table";
}; // show_sys_progress_indicator
// --------------------------------------------------------
Forum.handle_ajax_error = function(ajax, error, url, info)
{
  var mbuttons = [
    {
      caption: msg_OK,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  if(error == "Timeout")
  {
    error = msg_ErrTimeout.replace(/%s/, info.timeout / 1000);
  }
  else if(error == "NoResponse")
  {
    error = msg_ErrNoServerResponse;
  }
  else
  {
    error = msg_ErrRequestError + ":\n\n" + error;
    if(info.status) error += ' (' + info.status + ')';
  }

  Forum.show_user_msgbox(msg_Error, error, 'icon-error.gif', mbuttons);
}
// --------------------------------------------------------
Forum.handle_response_messages = function(response)
{
  var focus_element = function () {
    if(!response.FOCUS_ELEMENT) return;

    var elm = document.getElementById(response.FOCUS_ELEMENT);
    if(!elm) return;

    try
    {
      elm.focus();
    }
    catch(err)
    {
    }
  };

  var msg;

  var mbuttons = [
    {
      caption: msg_OK,
      handler: function() {
        Forum.hide_user_msgbox();
        focus_element();
      }
    }
  ];

  if(response.send_empty_hash_report)
  {
    send_empty_hash_report(response.send_empty_hash_report);
    response.send_empty_hash_report = 0;
  }
  
  if(response.ERROR_MESSAGE)
  {
    msg = response.ERROR_MESSAGE;
    response.ERROR_MESSAGE = null;
    Forum.show_user_msgbox(msg_Error, msg, 'icon-error.gif', mbuttons, false, function() { Forum.handle_response_messages(response); });
    return;
  }

  if(response.WARNING_MESSAGE)
  {
    msg = response.WARNING_MESSAGE;
    response.WARNING_MESSAGE = null;
    Forum.show_user_msgbox(msg_Warning, msg, 'icon-warning.gif', mbuttons, false, function() { Forum.handle_response_messages(response); });
    return;
  }

  if(response.PROG_WARNING)
  {
    msg = response.PROG_WARNING;
    response.PROG_WARNING = null;
    Forum.show_user_msgbox(msg_Warning, msg, 'icon-warning.gif', mbuttons, false, function() { Forum.handle_response_messages(response); });
    return;
  }

  if(response.INFO_MESSAGE)
  {
    msg = response.INFO_MESSAGE;
    response.INFO_MESSAGE = null;

    if(!response.AUTO_HIDE_INFO)
    {
      Forum.show_user_msgbox(msg_Information, msg, 'icon-info.gif', mbuttons, response.AUTO_HIDE_INFO, function() { Forum.handle_response_messages(response); });
      return;
    }
    else
    {
      if(no_success_report == 0) Forum.show_sys_bubblebox(msg_Information, msg, response.AUTO_HIDE_INFO);
      
      Forum.handle_response_messages(response);
      return;
    }
  }

  if(response.DEBUG_MESSAGE)
  {
    msg = response.DEBUG_MESSAGE;
    response.DEBUG_MESSAGE = null;

    debug_line(msg);
    show_debug_console(true);
  }
  
  window.setTimeout(focus_element, 200);
}; // handle_response_messages
// --------------------------------------------------------
Forum.invert_pair_checkbox = function(src_chbk, trg_id)
{
  var elm = document.getElementById(trg_id);

  if(!src_chbk.checked || !elm || !elm.checked) return;

  elm.checked = false;
}; // invert_pair_checkbox
// --------------------------------------------------------
function show_embedded_video(panel)
{
  var header = panel.parentNode;
  if(!header) return false;
  
  elm = header.nextSibling;
  if(!elm) return false;
  
  header.style.display = 'none';
  elm.style.display = 'block';
  
  return false;
} // show_embedded_video
// --------------------------------------------------------
function lightbox_image_clicked(event)
{
  event = event || window.event;

  if(event.preventDefault)
    event.preventDefault();
  else
    event.returnValue = false;
  
  if(event.stopPropagation)
    event.stopPropagation();

  Forum.show_image_preview(this);

  return false;
}
// --------------------------------------------------------
function init_telegram_widgets()
{
  var widgets = document.querySelectorAll('script[data-telegram-post]');
  var script = null;
  
  for (var i = 0; i < widgets.length; i++) {
    if(!widgets[i].getAttribute("data-block-id")) continue;
    
    script = document.createElement("script");

    script.setAttribute("src", widgets[i].getAttribute("src"));
    script.setAttribute("data-block-id", widgets[i].getAttribute("data-block-id"));
    script.setAttribute("data-telegram-post", widgets[i].getAttribute("data-telegram-post"));
    script.setAttribute("data-width", widgets[i].getAttribute("data-width"));
    
    widgets[i].parentNode.replaceChild(script, widgets[i]);    
  }
}
// --------------------------------------------------------
function init_reddit_widgets()
{
  var widgets = document.querySelectorAll('script[data-reddit-post]');
  var script = null;
  
  for (var i = 0; i < widgets.length; i++) {
    if(!widgets[i].getAttribute("data-block-id")) continue;
    
    script = document.createElement("script");

    script.setAttribute("src", widgets[i].getAttribute("src"));
    script.setAttribute("data-block-id", widgets[i].getAttribute("data-block-id"));
    script.setAttribute("data-reddit-post", widgets[i].getAttribute("data-reddit-post"));
    
    widgets[i].parentNode.replaceChild(script, widgets[i]);    
  }
}
// --------------------------------------------------------
function init_twitter_widgets()
{
  if(typeof twttr != 'undefined') setTimeout(function () { twttr.widgets.load(); }, 1000); 
}
// --------------------------------------------------------
function init_fb_widgets()
{
  if(typeof FB != 'undefined' && 
     typeof FB.XFBML != 'undefined' &&
     typeof FB.XFBML.parse == 'function') setTimeout(function () { FB.XFBML.parse(); }, 1000);
}
// --------------------------------------------------------
function init_embedded_widgets()
{
  init_twitter_widgets();
  init_fb_widgets();
  init_telegram_widgets();
  init_reddit_widgets();
} // init_embedded_widgets
// --------------------------------------------------------
function init_lightbox_images()
{
  var lightbox_images = document.getElementsByClassName("lightbox_image");
  if(lightbox_images.length == 0) return;

  var i;

  for(i = 0; i < lightbox_images.length; i++)
  {
    if(lightbox_images[i].lightbox_image_initialized) continue;

    lightbox_images[i].lightbox_image_initialized = true;

    Forum.addXEvent(lightbox_images[i], 'click', lightbox_image_clicked);
  }
}
// --------------------------------------------------------
function toogle_adjacent_page_jumper(elm)
{
  var _parent = elm.parentNode;
  var jumpers = null;
  var elms = null;
  
  while(_parent)
  {
    if(_parent.classList.contains("navigator_bar"))  
    {
      jumpers = _parent.querySelectorAll('.page_jumper');
      var cnt = jumpers.length;
      for(var i = 0; i < cnt; i++)
      {
        if(jumpers[i].style.display == "none")
        {
          jumpers[i].style.display = "block";
          
          elms = jumpers[i].querySelectorAll('input[name=page]');
          if(elms.length > 0) 
          {
            elms[0].focus();
            elms[0].select();
            elms[0].i_am_still_active = true;
            elms[0].my_jumper = jumpers[i];
            
            Forum.addXEvent(elms[0], "focus", function () {
                this.i_am_still_active = true;
            });
            Forum.addXEvent(elms[0], "blur", function () {
                this.i_am_still_active = false;
                var me = this;
                setTimeout(function () {
                    if (!me.i_am_still_active) me.my_jumper.style.display = "none";
                }, 600);
            });
          }
        }
        else
        {
          jumpers[i].style.display = "none";
        }
      }
      
      break;
    }
    
    _parent = _parent.parentNode;
  }  
  
  return false;
} // toogle_adjacent_page_jumper
// --------------------------------------------------------
function goto_page(form)
{
  if(isNaN(parseInt(form.elements["page"].value)))
  {
    form.elements["page"].value = form.elements["current_page"].value;
    return false;
  }
  
  if(form.elements["page"].value == form.elements["current_page"].value)
  {
    return false;
  }

  var page = parseInt(form.elements["page"].value);
  
  if(page < 1) form.elements["page"].value = 1;
  if(page > parseInt(form.elements["last_page"].value)) form.elements["page"].value = form.elements["last_page"].value;
  
  return delay_redirect(form.action.replace(/\$/, form.elements["page"].value));  
} // goto_page
// --------------------------------------------------------
function goto_post_page(form)
{
  if(isNaN(parseInt(form.elements["page"].value)))
  {
    form.elements["page"].value = form.elements["current_page"].value;
    return false;
  }
  
  if(form.elements["page"].value == form.elements["current_page"].value && !all_page_mode)
  {
    return false;
  }
  
  store_unposted_message();
  
  var page = parseInt(form.elements["page"].value);
  var current_page = parseInt(form.elements["current_page"].value);
  
  if(page < 1) form.elements["page"].value = 1;
  if(page > parseInt(form.elements["last_page"].value)) form.elements["page"].value = form.elements["last_page"].value;
  
  page = parseInt(form.elements["page"].value);
  
  var jump_distance;
  
  if(all_page_mode)
  {
    if(page > parseInt(form.elements["last_page"].value) / 2)
    {
      jump_distance = parseInt(form.elements["last_page"].value) - page + 1;
      return delay_redirect(form.action + "&startmsg=last" + "&offset=-" + jump_distance);
    }
    else
    {
      jump_distance = page - 1;
      return delay_redirect(form.action + "&startmsg=first" + "&offset=" + jump_distance);  
    }
  }
  
  if(page == 1)
  {
    return delay_redirect(form.action);  
  }
  else if(page == parseInt(form.elements["last_page"].value))
  {
    return delay_redirect(form.action + "&startmsg=last&offset=-1");  
  }
  else if(page - current_page == 1)
  {
    return delay_redirect(form.action + "&startmsg=" + last_message + "&offset=1");  
  }
  else if(page - current_page == -1)
  {
    return delay_redirect(form.action + "&startmsg=" + first_message + "&offset=-1");  
  }
  
  var offset = page - parseInt(form.elements["current_page"].value);
  var mode = "from_current";
  jump_distance = Math.abs(offset);
  
  if(parseInt(form.elements["last_page"].value) - page < jump_distance)
  {
    mode = "from_last";
    jump_distance = parseInt(form.elements["last_page"].value) - page + 1;
  }
  
  if(page < jump_distance)
  {
    mode = "from_first";
    jump_distance = page;
  }
  
  if(jump_distance > 100)
  {
    mode = "gotopage";
  }
  
  switch(mode)
  {
    case "gotopage": return delay_redirect(form.action + "&tpage=" + page);  
    case "from_last": return delay_redirect(form.action + "&startmsg=last" + "&offset=-" + jump_distance);  
    case "from_first": return delay_redirect(form.action + "&startmsg=first" + "&offset=" + jump_distance);  
  }
  
  if(offset < 0)
  {
    return delay_redirect(form.action + "&startmsg=" + first_message + "&offset=" + offset);  
  }
  else
  {
    return delay_redirect(form.action + "&startmsg=" + last_message + "&offset=" + offset);  
  }
} // goto_post_page
// --------------------------------------------------------
function prepare_for_navigation(link)
{
  var ev = window.event;  // Event object 'ev'
  var key = ev.which || ev.keyCode; // Detecting keyCode

  var ctrl = ev.ctrlKey ? ev.ctrlKey : ((key === 17) ? true : false);
  
  if(ctrl) return true;
  
  Forum.show_sys_progress_indicator(true);
  
  redirection_in_pogress = true;

  break_check_new_messages();
  
  return true;
} // prepare_for_navigation
// --------------------------------------------------------
function delay_redirect(url)
{
  Forum.show_sys_progress_indicator(true);
  
  redirection_in_pogress = true;

  break_check_new_messages();
  
  // We redirect with a little delay.

  setTimeout(function () {

    window.location.assign(url);

  }, 200);
  
  return false;
} // delay_redirect
// --------------------------------------------------------
function delay_reload()
{
  redirection_in_pogress = true;

  break_check_new_messages();

  // We reload with a little delay.
  // Sometimes, the reloading can fail by bad connection quality.
  // We start timer over the setInterval, that will try to reload again.
  // If the reload succeeds, the page is reloaded and the timer disapears.

  setTimeout(function () {

    window.location.reload(true);

  }, 200);
} // delay_reload
// --------------------------------------------------------
var check_new_messages_active = false;
var writing_message = false;
var do_not_check_new = false;
// --------------------------------------------------------
function activate_check_new_messages()
{
  if(typeof redirection_in_pogress != 'undefined' && redirection_in_pogress == true) return;
  
  check_new_messages_active = true;
}
// --------------------------------------------------------
function deactivate_check_new_messages()
{
  check_new_messages_active = false;
}
// --------------------------------------------------------
function break_check_new_messages()
{
  check_new_messages_active = false;

  if(check_new_messages_ajax && check_new_messages_ajax.running)
  {
    check_new_messages_ajax.abort();
  }
} // break_check_new_messages
// --------------------------------------------------------
var check_new_messages_ajax = null;

function check_new_messages()
{
  if(!check_new_messages_active || writing_message || do_not_check_new) return;

  if(check_new_messages_ajax && check_new_messages_ajax.running)
  {
    return;
  }

  if(!check_new_messages_ajax)
  {
    check_new_messages_ajax = new Forum.AJAX();

    check_new_messages_ajax.timeout = TIMEOUT;

    check_new_messages_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        if(!response.success) return;

        var news;
        var elm;

        if(response.protection_hash)
        {
          set_protection_hash(response.protection_hash);
        }
        
        if(response.new_messages_count > 0)
        {
          news = document.getElementsByClassName("new_messages_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.new_messages_count;
          }

          elm = document.getElementById("new_messages_alertbox");
          if(elm) elm.style.display = "block";

          news = document.getElementsByClassName("new_messages_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
          
          signal_new(true);
        }
        else
        {
          if(typeof unset_topic_new_markers == 'function') unset_topic_new_markers();
        }

        // new in forums
        if(response.topics_with_new_count > 0)
        {
          news = document.getElementsByClassName("topics_with_new_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.topics_with_new_count;
          }

          news = document.getElementsByClassName("topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }
        
        // new in favourites
        if(response.favourites_with_new_count > 0)
        {
          news = document.getElementsByClassName("favourites_with_new_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.favourites_with_new_count;
          }

          news = document.getElementsByClassName("favourites_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("favourites_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }

        // new in my topics
        if(response.my_topics_with_new_count > 0)
        {
          news = document.getElementsByClassName("my_topics_with_new_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.my_topics_with_new_count;
          }

          news = document.getElementsByClassName("my_topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("my_topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }
        
        // new in my part topics
        if(response.my_part_topics_with_new_count > 0)
        {
          news = document.getElementsByClassName("my_part_topics_with_new_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.my_part_topics_with_new_count;
          }

          news = document.getElementsByClassName("my_part_topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("my_part_topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }

        // private
        if(response.private_topics_with_new_count > 0)
        {
          news = document.getElementsByClassName("private_topics_with_new_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.private_topics_with_new_count;
          }

          news = document.getElementsByClassName("private_topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("private_topics_with_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }

        // events
        if(response.new_events_count > 0)
        {
          news = document.getElementsByClassName("new_events_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.new_events_count;
          }

          news = document.getElementsByClassName("new_events_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("new_events_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }
        
        // mod events
        if(response.new_mod_events_count > 0)
        {
          news = document.getElementsByClassName("new_mod_events_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.new_mod_events_count;
          }

          news = document.getElementsByClassName("new_mod_events_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("new_mod_events_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }

        // subscriptions
        if(response.subscription_authors_new_messages_count > 0)
        {
          news = document.getElementsByClassName("subscription_authors_new_messages_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.subscription_authors_new_messages_count;
          }

          news = document.getElementsByClassName("subscription_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("subscription_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }

        if(response.subscription_authors_new_topics_count > 0)
        {
          news = document.getElementsByClassName("subscription_authors_new_topics_count");
          for(var i = 0; i < news.length; i++)
          {
            news[i].innerHTML = response.subscription_authors_new_topics_count;
          }

          news = document.getElementsByClassName("subscription_topics_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "inline";
          }
        }
        else
        {
          news = document.getElementsByClassName("subscription_topics_new_indicator");
          for(var i = 0; i < news.length; i++)
          {
            news[i].style.display = "none";
          }
        }
        
        // author list

        var author;
        var authors = document.getElementsByClassName("subscription_author_new_messages");
        for(var i = 0; i < authors.length; i++)
        {
          author = authors[i].getAttribute('data-author');
          if(!author) continue;
          
          elm = authors[i].getElementsByClassName("new_messages_count");
          
          if(response.subscription_author_new_messages && response.subscription_author_new_messages[author] > 0)
          {
            if(elm.length > 0) elm[0].innerHTML = response.subscription_author_new_messages[author];
            authors[i].style.display = "inline";
          }
          else
          {
            authors[i].style.display = "none";
            if(elm.length > 0) elm[0].innerHTML = 0;
          }
        }

        authors = document.getElementsByClassName("subscription_author_new_topics");
        for(var i = 0; i < authors.length; i++)
        {
          author = authors[i].getAttribute('data-author');
          if(!author) continue;
          
          elm = authors[i].getElementsByClassName("new_messages_count");
          
          if(response.subscription_author_new_topics && response.subscription_author_new_topics[author] > 0)
          {
            if(elm.length > 0) elm[0].innerHTML = response.subscription_author_new_topics[author];
            authors[i].style.display = "inline";
          }
          else
          {
            authors[i].style.display = "none";
            if(elm.length > 0) elm[0].innerHTML = 0;
          }
        }
        
        // topic list
        
        var tid;
        var topics = document.getElementsByClassName("new_messages_indicator");
        for(var i = 0; i < topics.length; i++)
        {
          tid = topics[i].getAttribute('data-tid');
          if(!tid) continue;
          
          if(response.ignored_topics && response.ignored_topics[tid] > 0)
          {
            topics[i].classList.add("topic_ignored");
          }
          else
          {
            topics[i].classList.remove("topic_ignored");
          }
          
          elm = topics[i].getElementsByClassName("new_messages_count");
          
          if(response.topics_with_new && response.topics_with_new[tid] > 0)
          {
            if(elm.length > 0) elm[0].innerHTML = response.topics_with_new[tid];
            topics[i].style.display = "inline";
            
            if(response.never_visited_topics && response.never_visited_topics[tid]) 
              topics[i].classList.add("never_visited_topic");
            else                                   
              topics[i].classList.remove("never_visited_topic"); 
          }
          else
          {
            topics[i].style.display = "none";
            if(elm.length > 0) elm[0].innerHTML = 0;
          }
        }
        
        topics = document.getElementsByClassName("other_new_messages_alertbox");
        for(var i = 0; i < topics.length; i++)
        {
          tid = topics[i].getAttribute('data-tid');
          if(!tid) continue;
          
          elm = topics[i].getElementsByClassName("new_messages_count");
          
          if(response.topics_with_new && response.topics_with_new[tid] > 0)
          {
            if(elm.length > 0) elm[0].innerHTML = response.topics_with_new[tid];
            topics[i].style.visibility = "visibile";
          }
          else
          {
            topics[i].style.visibility = "hidden";
            if(elm.length > 0) elm[0].innerHTML = 0;
          }
        }

        // forum list
        
        var fid;
        var forums = document.getElementsByClassName("forum_with_new_indicator");

        for(var i = 0; i < forums.length; i++)
        {
          fid = forums[i].getAttribute('data-fid');
          if(!fid) continue;
          
          elm = forums[i].getElementsByClassName("topics_with_new_count");
          
          if(response.not_preferred_forums && response.not_preferred_forums[fid] > 0)
          {
            forums[i].classList.add("topic_ignored");
          }
          else
          {
            forums[i].classList.remove("topic_ignored");
          }

          if(response.forums_with_new && response.forums_with_new[fid] > 0)
          {
            if(elm.length > 0) elm[0].innerHTML = response.forums_with_new[fid];
            forums[i].style.display = "inline";
          }
          else
          {
            forums[i].style.display = "none";
            if(elm.length > 0) elm[0].innerHTML = 0;
          }
        }
      }
      catch(err)
      {
      }
    };

    check_new_messages_ajax.onerror = function(error, url, info)
    {
    };
  }

  check_new_messages_ajax.abort();
  check_new_messages_ajax.resetParams();

  check_new_messages_ajax.setPOST('hash', get_protection_hash());
  check_new_messages_ajax.setPOST('user_logged', user_logged);
  check_new_messages_ajax.setPOST('trace_sql', trace_sql);
  if(typeof topic_id != 'undefined') check_new_messages_ajax.setPOST('tid', topic_id);

  check_new_messages_ajax.setPOST('fpage', fpage);

  check_new_messages_ajax.request("ajax/check_new.php");
}
// --------------------------------------------------------
var clear_profile_data_ajax = null;

function clear_profile_data()
{
  Forum.show_sys_progress_indicator(true);

  if(!clear_profile_data_ajax)
  {
    clear_profile_data_ajax = new Forum.AJAX();

    clear_profile_data_ajax.timeout = TIMEOUT;

    clear_profile_data_ajax.beforestart = function() { break_check_new_messages(); };
    clear_profile_data_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    clear_profile_data_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          delay_reload();
          return;
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    clear_profile_data_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  clear_profile_data_ajax.abort();
  clear_profile_data_ajax.resetParams();

  clear_profile_data_ajax.setPOST('clear_profile_data', "1");
  clear_profile_data_ajax.setPOST('hash', get_protection_hash());
  clear_profile_data_ajax.setPOST('user_logged', user_logged);
  clear_profile_data_ajax.setPOST('trace_sql', trace_sql);

  clear_profile_data_ajax.request("ajax/process.php");

  return false;
} // clear_profile_data
// --------------------------------------------------------
var switch_skin_ajax = null;

function switch_skin(skin)
{
  Forum.show_sys_progress_indicator(true);

  if(!switch_skin_ajax)
  {
    switch_skin_ajax = new Forum.AJAX();

    switch_skin_ajax.timeout = TIMEOUT;

    switch_skin_ajax.beforestart = function() { break_check_new_messages(); };
    switch_skin_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    switch_skin_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success)
        {
          var url = window.location.href.replace(/(\?|&)(mobile|tablet|desktop)=[^&]*/, "");
          
          delay_redirect(url);
          return;
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);
    };

    switch_skin_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);
    };
  }
  
  switch_skin_ajax.abort();
  switch_skin_ajax.resetParams();

  switch_skin_ajax.setPOST('switch_skin', skin);
  switch_skin_ajax.setPOST('hash', get_protection_hash());

  switch_skin_ajax.request("ajax/process.php");

  return false;
} // switch_skin
// --------------------------------------------------------
var tab_left = false;
function signal_new(state)
{
  var fav_icon = document.getElementById("fav_icon");
  if(!fav_icon) return;
  
  if(!fav_icon.getAttribute("data-default-icon") || !fav_icon.getAttribute("data-signal-icon")) return;
  
  var tmp;
  
  if(state && document.hidden && tab_left)
  {
    fav_icon.href = fav_icon.getAttribute("data-signal-icon");
    fav_icon.setAttribute("data-alternative-icon", fav_icon.getAttribute("data-default-icon"));
  }
  else
  {
    fav_icon.href = fav_icon.getAttribute("data-default-icon");
  }
} // signal_new
// --------------------------------------------------------
Forum.addXEvent(window, 'load', function () {
  init_lightbox_images();

  window.setTimeout(function () {
    activate_check_new_messages();
    check_new_messages();

    if(typeof NEW_CHECK_FREQUENCY == 'undefined') NEW_CHECK_FREQUENCY = 30000;
    
    window.setInterval(function() { check_new_messages(); }, NEW_CHECK_FREQUENCY);
  }, 1000);
});
// --------------------------------------------------------
// by coming back to the tab, recheck the new messages
Forum.addXEvent(document, 'visibilitychange', function () { 
  if(!document.hidden) 
  {
    tab_left = false;
    signal_new(false);
    refresh_captcha();    
    check_new_messages();
  }
  else
  {
    tab_left = true;
  }
});
// --------------------------------------------------------
var history_undo_actions_stack = new Array();
var refresh_or_history_navigation = false;

var get_history_url = function () {
  debug_line("history url: " + document.location.href, 'history');
  return document.location.href;
};

if(document.location.href.indexOf('topic.php') == -1 && 
   document.location.href.indexOf('search_topic.php') == -1 &&
   document.location.href.indexOf('new_topic.php') == -1)
{
  debug_line("Default history intialization", 'history');
  Forum.addXEvent(window, 'DOMContentLoaded', function () {
    if(window.history.state && (window.history.state.initial_stage || window.history.state.work_stage))
    {
      debug_line("Refresh or history back", 'history');
      refresh_or_history_navigation = true;

      // we deactivate the current step if refresh was done while writing or previewing of the image
      if(window.history.state.work_stage)
      {
        window.history.replaceState({ work_stage: 1, is_active: 0 }, null, get_history_url());
      }
    }
    else
    {
      debug_line("New entrance", 'history');
      window.history.replaceState({ inital_stage: 1 }, null, get_history_url());
      window.history.pushState({ work_stage: 1 }, null, get_history_url());
    }
    
    debug_line('Go-back action put to the stack', 'history');
    history_undo_actions_stack.push(function () {
      debug_line('doing back', 'history');
      window.history.back();
    });
  });
}
// --------------------------------------------------------
Forum.addXEvent(window, 'popstate', function(e) {
  if(typeof e.state == 'undefined' || !e.state) 
  {
    return;
  }
  
  if(e.state.inital_stage || e.state.work_stage)
  {
    debug_line('popstate: popping the history action ...', 'history');
    var action = history_undo_actions_stack.pop();
    if(action) action.call();
  }
});  
// --------------------------------------------------------

// --------------------------------------------------------
Forum.add_sys_message_handler();
// --------------------------------------------------------

