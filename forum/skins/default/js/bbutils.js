//----------------------------------------------------------------------
function convert_quote_to_bbcode(quote, quote_level)
{
  // We need to omit citates if the author is ignored.
  // We could do getComputedStyle(quote) but
  // Opera and Chrome return empty getComputedStyle object
  // if the element is not attached to the document.
  // We create a temporary object, add it to body, add the classes to
  // it, and do getComputedStyle.
  // Then we remove it.
  
  /*
  var dummy_quote_wrapper = document.createElement('div');
  dummy_quote_wrapper.style.height = '0px';

  for(var i = 0; i < quote.classList.length; i++) 
  {
    dummy_quote_wrapper.classList.add(quote.classList.item(i));
  }
  
  document.body.appendChild(dummy_quote_wrapper);

  var dummy_quote = document.createElement('div');
  dummy_quote.classList.add('quote');
  dummy_quote_wrapper.appendChild(dummy_quote);
  
  var style = getComputedStyle(dummy_quote);

  dummy_quote_wrapper.parentNode.removeChild(dummy_quote_wrapper);
  */

  var quote_hidden = false;
  var omit_cmid = false;
  var title = "";
  if(quote.hasAttribute('data-author')) title = quote.getAttribute('data-author');

  if(quote.classList.contains("ignored_author"))
  {
    quote_hidden = true;
  }

  if(quote.classList.contains("strongly_ignored_author"))
  {
    omit_cmid = true;
    quote_hidden = true;
    title = "[" + msg_ignored + "]";
  }

  var body = "";
  var bodies = quote.getElementsByClassName('quote');
  if(bodies.length > 0)
  {
    if(!archive_mode && bodies[0].hasAttribute('data-cmid')) title += '#' + bodies[0].getAttribute('data-cmid');

    if(quote_hidden)
    {
      return "\n\n[b]" + title + "[/b]\n\n";
    }

    body = convert_nodes_to_bbcode(bodies[0], quote_level);
  }
  
  if(body == "") return "";
  
  if(quote_level > 4) body = "...";
  
  return "\n\n[quote=" + title + "]\n" + body + "\n[/quote]\n\n";
} // convert_quote_to_bbcode
//----------------------------------------------------------------------
function convert_spoiler_to_bbcode(quote, quote_level)
{
  var title = "";
  var titles = quote.getElementsByClassName('spoiler_header');
  if(titles.length > 0)
  {
    title = titles[0].innerHTML;
  }
  
  var body = "";
  var bodies = quote.getElementsByClassName('spoiler');
  if(bodies.length > 0)
  {
    body = convert_nodes_to_bbcode(bodies[0], quote_level);
  }
  
  if(body == "") return "";
  
  if(title != "") title = "=" + title;
  
  return "\n\n[spoiler" + title + "]\n" + body + "\n[/spoiler]\n\n";
} // convert_spoiler_to_bbcode
//----------------------------------------------------------------------
function convert_code_to_bbcode(code, quote_level)
{
  var lang = code.getAttribute('data-code');
  if(lang == '' || lang == 'nohighlight') lang = 'text';
  
  var body = "";
  var highlight = null;
  var bodies = code.getElementsByTagName('code');
  if(bodies.length > 0)
  {
    var highlights = bodies[0].getElementsByClassName('code_highlight');
    for(var i = highlights.length - 1; i >= 0 ; i--)
    {
      highlight = highlights[i].innerHTML;
      highlight = highlight.replace(new RegExp("<span class=\"hljs-[^\"]+\">", "g"), "");
      highlight = highlight.replace(new RegExp("<span class=\"code_highlight\">", "g"), "");
      highlight = highlight.replace(new RegExp("</span>", "g"), "");
      
      highlight = "==>" + Forum.decode_html(highlight) + "<==";
      
      bodies[0].replaceChild(document.createTextNode(highlight), highlights[i]);
    }
    
    body = bodies[0].innerHTML;
    body = body.replace(new RegExp("<span class=\"hljs-[^\"]+\">", "g"), "");
    body = body.replace(new RegExp("</span>", "g"), "");
    body = Forum.decode_html(body);
  }
  
  if(body == "") return "";
  
  return "\n\n[code=" + lang + "]" + body + "[/code]\n\n";
} // convert_code_to_bbcode
//----------------------------------------------------------------------
function convert_list_to_bbcode(list_type, list, quote_level)
{
  var items = list.getElementsByTagName('li');
  if(items.length == 0) 
  {
    // Selection a text within one LI node results
    // for some reason in <ol>selected text ...</ol>.
    // So, if the list does not contain any LI, we try just to extract
    // the text from it.
    return convert_nodes_to_bbcode(list, quote_level);
  }
  
  var result = "\n\n[" + list_type + "]";
  
  for(var i = 0; i < items.length; i++)
  {
    result += convert_nodes_to_bbcode(items[i], quote_level) + "\n";
  }
  
  result += "[/" + list_type + "]\n\n";
  
  return result;
} // convert_list_to_bbcode
//----------------------------------------------------------------------
function convert_table_to_bbcode(table, quote_level)
{
  var row = "";
  var cell = "";
  var delimiter = table.getAttribute('data-delimiter');
  if(!delimiter) delimiter = ",";
  var rows = table.getElementsByTagName('tr');
  var cells = null;
  if(rows.length == 0) return "";
  
  var result = "\n\n[table";
  
  if(delimiter != ",") result += "=" + delimiter;
  
  result += "]";
  
  if (delimiter == "tab") delimiter = "\t";
  
  var no_header = true;
  
  for(var i = 0; i < rows.length; i++)
  {
    cells = rows[i].getElementsByTagName('td');
    if(cells.length == 0) 
    {
      cells = rows[i].getElementsByTagName('th');
      no_header = false;
    }
    if(cells.length == 0) continue;
    
    row = "";
    for(var j = 0; j < cells.length; j++)
    {
      colspan = cells[j].getAttribute("colspan");
      if(colspan !== null) colspan = "::" + colspan;
      else                 colspan = "";
      
      cell = convert_nodes_to_bbcode(cells[j], quote_level) + colspan;
      if(cell.indexOf(delimiter) != -1) 
      {
        cell = cell.replace(/"/g, '""');
        cell = '"' + cell + '"';
      }
      
      if (delimiter != "\t") row += cell + delimiter + " ";
      else                   row += cell + delimiter;
    }
    
    if(row != "")
    {
      if(i == 0 && no_header)
      {
        result += "-\n";
      }
      
      // remove last delimiter
      if (delimiter != "\t") row = row.substring(0, row.length-2) + "\n";
      else                   row = row.substring(0, row.length-1) + "\n";
      
      result += row;
    }
  }
  
  result += "[/table]\n\n";
  
  return result;
} // convert_table_to_bbcode
//----------------------------------------------------------------------
function convert_audio_to_bbcode(audio)
{
  var source = "";
  var sources = audio.getElementsByTagName('source');
  if(sources.length > 0)
  {
    source = sources[0].src;
  }
  
  if(source == "") return "";
  
  return "[audio]" + source + "[/audio]";
} // convert_audio_to_bbcode
//----------------------------------------------------------------------
function convert_video_to_bbcode(video)
{
  var source = "";
  var sources = video.getElementsByTagName('source');
  if(sources.length > 0)
  {
    source = sources[0].src;
  }
  
  if(source == "") return "";
  
  return "[video]" + source + "[/video]";
} // convert_video_to_bbcode
//----------------------------------------------------------------------
function convert_nodes_to_bbcode(container, quote_level)
{
  var current_node = null;
  var re, matches, tmp, text = '';
  
  for(var i = 0; i < container.childNodes.length; i++)
  {
    current_node = null;
    
    switch(container.childNodes[i].nodeName)
    {
      case 'FORM':
      current_node = document.createTextNode("");
      break;

      case 'HR':
      current_node = document.createTextNode("\n\n[hr]\n\n");
      break;

      case 'BR':
      current_node = document.createTextNode("");
      break;
      
      case '#comment':
      current_node = document.createTextNode("");
      break;      
      
      case 'STRONG':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      current_node = document.createTextNode('[b]' + text + '[/b]');
      break;
      
      case 'SUB':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      current_node = document.createTextNode('[sub]' + text + '[/sub]');
      break;
      
      case 'SUP':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      current_node = document.createTextNode('[sup]' + text + '[/sup]');
      break;
      
      case 'SUP':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      current_node = document.createTextNode('[sup]' + text + '[/sup]');
      break;
      
      case 'U':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      current_node = document.createTextNode('[u]' + text + '[/u]');
      break;

      case 'EM':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      current_node = document.createTextNode('[i]' + text + '[/i]');
      break;

      case 'PRE':
      if(container.childNodes[i].classList.contains('fixed'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }     

      if(container.childNodes[i].classList.contains('ascii_art'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        
        var bg = container.childNodes[i].getAttribute("data-bg");
        var fsize = container.childNodes[i].getAttribute("data-fsize");
        
        current_node = document.createTextNode("\n\n[ascii-art bg=" + bg + " fsize=" + fsize + "]" + text + "[/ascii-art]\n\n");
        break;
      }     
      
      // check whether it is a code
      var code = container.childNodes[i].getElementsByTagName('code');
      if(code.length > 0 && code[0].classList.contains('hljs'))
      {
        container.childNodes[i].setAttribute("data-code", code[0].getAttribute("data-code"));
        current_node = document.createTextNode(convert_code_to_bbcode(container.childNodes[i], quote_level));
        break;
      }

      // not matching, just remove the PRE tag
      //current_node = document.createTextNode(text);
      break;

      case 'IFRAME':
      if(container.childNodes[i].classList.contains('gmap_iframe'))
      {
        current_node = document.createTextNode("\n\n" + container.childNodes[i].getAttribute("data-bbcode") + "\n\n");
        break;
      }
      break;

      case 'DIV':
      if(container.childNodes[i].classList.contains('user_post_actions'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('post_ip_info'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('moderator_post_actions'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('version_container'))
      {
        current_node = document.createTextNode('');
        break;
      }      
      if(container.childNodes[i].classList.contains('post_rating'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('message_signature'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('moderator_warning'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('post_status_bar'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('update_info'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('navigation_arrows'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('scroll_up'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('scroll_down'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('tag'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('adult_tag'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('attachment_del_indicator'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('attachment_button'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('hidden_phrase_expander'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('hidden_phrase'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode('[hidden]' + text.trim() + '[/hidden]');
        break;
      }
      if(container.childNodes[i].classList.contains('fixed'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode("\n\n[fixed]" + text + "[/fixed]\n\n");
        break;
      }
      if(container.childNodes[i].classList.contains('poem'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode("\n\n[poem]" + text + "[/poem]\n\n");
        break;
      }
      if(container.childNodes[i].classList.contains('clear_both'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('citate_expander'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('tags_list'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('tags_list'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('attachment_link'))
      {
        current_node = document.createTextNode('');
        break;
      }      
      if(container.childNodes[i].classList.contains('spoiler_header'))
      {
        current_node = document.createTextNode('');
        break;
      }      
      if(container.childNodes[i].classList.contains('spoiler'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode("\n\n[spoiler]\n" + text + "\n[/spoiler]\n\n");
        break;
      }      
      if(container.childNodes[i].classList.contains('post_id_info'))
      {
        current_node = document.createTextNode('');
        break;
      }      
      if(container.childNodes[i].classList.contains('post_checkbox'))
      {
        current_node = document.createTextNode('');
        break;
      }      
      if(container.childNodes[i].classList.contains('short_video'))
      {
        current_node = document.createTextNode('');
        break;
      }      
      if(container.childNodes[i].classList.contains('emb_video_container'))
      {
        current_node = document.createTextNode(convert_nodes_to_bbcode(container.childNodes[i], quote_level));
        break;
      }      
      if(container.childNodes[i].classList.contains('table_wrapper'))
      {
        current_node = document.createTextNode(convert_nodes_to_bbcode(container.childNodes[i], quote_level));
        break;
      }      
      if(container.childNodes[i].classList.contains('popup_moderator_menu'))
      {
        current_node = document.createTextNode('');
        break;
      }            
      if(container.childNodes[i].classList.contains('manage_tags_list'))
      {
        current_node = document.createTextNode('');
        break;
      }      
      if(container.childNodes[i].classList.contains('message_text'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('picture_wrapper'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('thumb_gallery'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode("\n\n[gallery]\n\n" + text + "\n\n[/gallery]\n\n");
        break;
      }
      if(container.childNodes[i].classList.contains('quote_wrapper'))
      {
        current_node = document.createTextNode(convert_quote_to_bbcode(container.childNodes[i], quote_level + 1));
        break;
      }
      if(container.childNodes[i].classList.contains('spoiler_wrapper'))
      {
        current_node = document.createTextNode(convert_spoiler_to_bbcode(container.childNodes[i], quote_level));
        break;
      }
      if(container.childNodes[i].classList.contains('code_wrapper'))
      {
        current_node = document.createTextNode(convert_code_to_bbcode(container.childNodes[i], quote_level));
        break;
      }
      if(container.childNodes[i].classList.contains('media_wrapper'))
      {
        current_node = document.createTextNode(container.childNodes[i].getAttribute("data-bbcode"));
        break;
      }
      if(container.childNodes[i].classList.contains('emb_wrapper'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('attachment_wrapper'))
      {
        tmp = container.childNodes[i].getAttribute("data-attnr");
        if(!tmp || tmp == 1) tmp = '';
        
        if(container.childNodes[i].getAttribute("data-attgif"))
          current_node = document.createTextNode("\n\n[anim][attachment" + tmp + "=" + container.childNodes[i].getAttribute("data-attid") + "][/anim]");
        else
          current_node = document.createTextNode("\n\n[attachment" + tmp + "=" + container.childNodes[i].getAttribute("data-attid") + "]\n\n");
        break;
      }
      if(container.childNodes[i].classList.contains('topic_checkbox'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('citatable'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('message_text_more_wrapper'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('manage_tags_list'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('tags_list'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('add_tags'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('quote_header'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('qauthor'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('qauthor_ignored'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('qcitated'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('citated_message_link'))
      {
        text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
        current_node = document.createTextNode(text);
        break;
      }
      if(container.childNodes[i].classList.contains('gif_loading_animation'))
      {
        current_node = document.createTextNode('');
        break;
      }     
      
      // not matching, just remove the DIV tag
      // we commented it to catch unhuandled divs
      //current_node = document.createTextNode(text);
      break;
      
      case 'P':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      current_node = document.createTextNode("\n\n" + text + "\n\n");
      break;

      case 'SPAN':
      if(container.childNodes[i].classList.contains('separator'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('carma_plus'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('carma_minus'))
      {
        current_node = document.createTextNode('');
        break;
      }
      //-----------
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      //-----------
      if(container.childNodes[i].classList.contains('visible_author'))
      {
        if(container.classList.contains('strongly_ignored_author'))
        {
          current_node = document.createTextNode('');
        }
        else
        {
          if(container.childNodes[i].hasAttribute("data-ref-author"))
          {
            text = container.childNodes[i].getAttribute("data-ref-author");
          }

          if(!archive_mode && container.childNodes[i].hasAttribute("data-ref-mid"))
          {
            text += "#" + container.childNodes[i].getAttribute("data-ref-mid");
          }

          current_node = document.createTextNode(text);
        }
      }
      //-----------
      if(container.childNodes[i].classList.contains('invisible_author'))
      {
        if(container.classList.contains('strongly_ignored_author'))
          current_node = document.createTextNode(text);
        else
          current_node = document.createTextNode('');
      }
      //-----------
      if(container.childNodes[i].style.color != '')
      {
        current_node = document.createTextNode('[color=' + rgb2hex(container.childNodes[i].style.color) + ']' + text + '[/color]');
        break;
      }
      //-----------
      if(container.childNodes[i].style.textDecoration == 'line-through')
      {
        current_node = document.createTextNode('[s]' + text + '[/s]');
        break;
      }
      //-----------
      re = new RegExp("^size(\\d+)$", "");
      matches = re.exec(container.childNodes[i].className);
      if(matches)
      {
        current_node = document.createTextNode('[size=' + matches[1] + ']' + text + '[/size]');
        break;
      }
      //-----------
      if(container.childNodes[i].className == 'pinned_sign')
      {
        current_node = document.createTextNode(text);
        break;
      }
      //-----------
      if(container.childNodes[i].className == 'dummy')
      {
        current_node = document.createTextNode(text);
        break;
      }
      //-----------
      if(container.childNodes[i].className == 'citation_expander')
      {
        current_node = document.createTextNode('');
        break;
      }
      //-----------
      if(container.childNodes[i].className == 'qdate')
      {
        current_node = document.createTextNode('');
        break;
      }
      //-----------
      // text higjhlighting after search, just remove the tag
      if(container.childNodes[i].className == 'found_key')
      {
        current_node = document.createTextNode(text);
        break;
      }
      //-----------
      // not matching, just remove the SPAN tag
      // we commented it to catch unhuandled spans
      //current_node = document.createTextNode(text);
      break; // SPAN

      case 'A':
      text = convert_nodes_to_bbcode(container.childNodes[i], quote_level);
      if(container.childNodes[i].classList.contains('post_action_command'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('moderator_link'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('attachment_link'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('attachment_image'))
      {
        current_node = document.createTextNode('');
        break;
      }
      if(container.childNodes[i].classList.contains('lightbox_image'))
      {
        current_node = document.createTextNode("\n\n[img]" + container.childNodes[i].href + "[/img]\n\n");
        break;
      }
      if(container.childNodes[i].hasAttribute('data-spec-link'))
      {
        if(archive_mode && /#\d+/.test(container.childNodes[i].getAttribute('data-spec-link')))
          current_node = document.createTextNode("");
        else      
          current_node = document.createTextNode(container.childNodes[i].getAttribute('data-spec-link'));
        break;
      }
      
      if(container.childNodes[i].href == text || container.childNodes[i].href == text + '/')
      {
        current_node = document.createTextNode('[url]' + text + '[/url]');
      }
      else
      {
        current_node = document.createTextNode('[url=' + container.childNodes[i].href + ']' + text + '[/url]');
      }
      break;

      case 'IMG':
      if(container.childNodes[i].classList.contains('bbcode_external_smiley'))
      {
        current_node = document.createTextNode('[smile]' + container.childNodes[i].src + '[/smile]');
        break;
      }
      if(container.childNodes[i].classList.contains('bbcode_smiley'))
      {
        current_node = document.createTextNode(container.childNodes[i].title);
        break;
      }
      if(container.childNodes[i].classList.contains('gif_placeholder'))
      {
        current_node = document.createTextNode("\n\n[anim]" + container.childNodes[i].getAttribute('data-src') + "[/anim]\n\n");
        break;
      }
      //-----------
      // not matching, just remove the IMG tag
      current_node = document.createTextNode('');
      break;
      
      case 'UL':
      current_node = document.createTextNode(convert_list_to_bbcode("list", container.childNodes[i], quote_level));
      break;
      
      case 'OL':
      current_node = document.createTextNode(convert_list_to_bbcode("nlist", container.childNodes[i], quote_level));
      break;
      
      case 'LI':
      current_node = document.createTextNode(text);
      break;
      
      case 'TABLE':
      current_node = document.createTextNode(convert_table_to_bbcode(container.childNodes[i], quote_level));
      break;

      case 'AUDIO':
      current_node = document.createTextNode(convert_audio_to_bbcode(container.childNodes[i]));
      break;

      case 'VIDEO':
      current_node = document.createTextNode(convert_video_to_bbcode(container.childNodes[i]));
      break;

      case 'TR':
      current_node = document.createTextNode('');
      break;

      case 'TH':
      current_node = document.createTextNode('');
      break;

      case 'TD':
      if(!container.childNodes[i].classList.contains('csv_table_cell'))
      {
        current_node = document.createTextNode('');
      }
      if(!container.childNodes[i].classList.contains('message_action_cell'))
      {
        current_node = document.createTextNode('');
      }
      if(!container.childNodes[i].classList.contains('post_footer'))
      {
        current_node = document.createTextNode('');
      }
      break;
  
      case 'TH':
      if(!container.childNodes[i].classList.contains('csv_table_cell'))
      {
        current_node = document.createTextNode('');
      }
      if(!container.childNodes[i].classList.contains('message_action_cell'))
      {
        current_node = document.createTextNode('');
      }
      break;
    }
    
    if(current_node !== null)
    {
      container.replaceChild(current_node, container.childNodes[i]);
    }
  }
  
  return Forum.decode_html(container.innerHTML);
} // convert_nodes_to_bbcode
//----------------------------------------------------------------------
