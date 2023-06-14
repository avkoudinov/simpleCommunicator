function delete_attachment_file(index)
{
  var elm = document.getElementById('attachment' + index);
  if(!elm) return;
  
  // User hits the del button
  
  // delete possible pasted attachment from buffer
  delete_attachment_from_buffer(index);
  
  // set the delete flag to delete possible existing attachment of the edited post
  // the flag will be sent to server
  set_attachment_delete_flag(index);

  resetFileField(elm);

  var pid = "";  
  if(elm.form.elements["edited_post"]) pid = elm.form.elements["edited_post"].value;
  
  var re = new RegExp("(\\[(gif|anim)\\])?\\[attachment" + index + "(=" + pid + ")?\\](\\[/(gif|anim)\\])?", "gi");
  
  elm.form.elements["message"].value = elm.form.elements["message"].value.replace(re, "");
}

function reset_attachment_fields_and_slots()
{
  reset_attachment_buffer();
  
  for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    idx = (i == 1) ? '' : i;
    elm = document.getElementById('attachment' + idx);
    if(!elm) continue;
    
    elm.setAttribute('data-original_attachment_exists', 0);
  }
  
  show_hide_additional_attachments_area();
}

function show_attachment_delete_button(index)
{
  var del = document.getElementById('del_attachment_button' + index);
  if(del) del.parentNode.parentNode.classList.add("del_visible");
  
  var elm = document.getElementById('attachment' + index);
  if(elm) resizeFileInputControl(elm);
}

function show_hide_attachment_paste_options(index)
{
  var elm = document.getElementById('attachment' + index);
  var lnk = document.getElementById('paste_attachment' + index);
  var del = document.getElementById('del_attachment_button' + index);
  var gif_lnk = document.getElementById('paste_attachment_gif' + index);

  if(!elm || !lnk || !del || !gif_lnk) return;
  
  var file_name = elm.value;
  if(attachment_buffer["attachment" + index])
  {
    file_name = attachment_buffer["attachment" + index].file_name;
  }
  
  if(file_name == "") 
  {
    lnk.style.display = "none";
    gif_lnk.style.display = "none";
    del.parentNode.parentNode.classList.remove("del_visible");
    
    // by deletion in the edit mode, we need a special text in the file field
    // that the current attachment on the server will be deleted
    if(elm.getAttribute("data-original_attachment_exists") == 1)
    {
      setFileInputCaption(elm, msg_DeleteCurrentAttachment, false);
    }
  }
  else                
  {
    setFileInputCaption(elm, file_name.replace(/\\/g, '/').replace( /.*\//, ''), false);

    del.parentNode.parentNode.classList.add("del_visible");

    lnk.style.display = "inline";
    
    var re = new RegExp(".*\\.(gif|webp)$", "i");
    if(re.test(file_name))
      gif_lnk.style.display = "inline";
    else
      gif_lnk.style.display = "none";
  }

  return false;
}

function show_hide_additional_attachments_area()
{
  var has_any_attachment = false;
  
  var i, index;
  var elm;
  
  for(i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    index = i;
    if(index == 1) index = '';
    
    elm = document.getElementById('attachment' + index);
    if(!elm) continue;
    
    if (elm.value || elm.getAttribute("data-original_attachment_exists") == 1 || attachment_buffer["attachment_slot_locked" + index])
    {
      has_any_attachment = true;
    }
  }
  
  elm = document.getElementById('additional_attachments_area');
  if(!has_any_attachment)
  {
    if(elm) elm.style.display = 'none';
  }
  else
  {
    if(elm) elm.style.display = 'block';
  }
    
  for(i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    index = i;
    if(index == 1) index = '';

    elm = document.getElementById('attachment' + index);
    if(!elm) continue;

    Forum.fireEvent(elm, 'show');
  }

  return false;
}

function paste_attachment_placeholder(index, tag)
{
  var message = document.getElementById('message');
  if(!message) return false;

  var attachment = "";
  var i, idx;
  var elm;
  
  if (tag == "gallery")
  {
    for(i = 1; i <= ATTACHMENTS_PER_POST; i++)
    {
      idx = i;
      if(idx == 1) idx = '';
      
      elm = document.getElementById('attachment' + idx);
      if(!elm) continue;
      
      if (elm.value || elm.getAttribute("data-original_attachment_exists") == 1 || attachment_buffer["attachment_slot_locked" + idx])
      {
        attachment += "[attachment" + idx + "]\n";
      }
      else
      {
      }
    }
    
    if (attachment) 
    {
      attachment = "\n\n[gallery]\n" + attachment + "[/gallery]\n\n";
    }
  }
  else if(tag) 
  {
    attachment = "\n\n[" + tag + "][attachment" + index + "][/" + tag + "]\n\n";
  }
  else
  {
    attachment = "\n\n[attachment" + index + "]\n\n";
  }
  
  if (!attachment) return;

  if (document.selection)
  {
    // ie & may be opera 8
    var rng = document.selection.createRange();
    rng.text = attachment;
  }
  else if (message.selectionStart ||
           message.selectionStart == '0')
  {
    // mozilla: intellegent bcodes support
    var selStart = message.selectionStart;
    var selEnd = message.selectionEnd;

    var s = message.value;
    s = s.substring(0, selStart) + attachment + s.substring(selEnd, s.length);
    message.value = s;

    message.setSelectionRange(selStart + attachment.length, selStart + attachment.length);
  }
  else
  {
    message.value += attachment;
  }

  message.focus();

  return false;
}

function on_attachment_pasted(index)
{
  show_hide_attachment_paste_options(index); 
  
  show_hide_additional_attachments_area();  
}

function on_attachment_changed(index)
{
  elm = document.getElementById('attachment' + index);
  if(!elm) return;
  
  if(elm.files.length == 0) {
    show_hide_attachment_paste_options(index); 
    
    show_hide_additional_attachments_area();  
    
    return;
  }
  
  for (var i = 0; i < elm.files.length; i++) {
    if(i == 0)
      replace_attachment_in_buffer({ file_name: elm.files[i].name, file: elm.files[i] }, index);
    else
    {
      if(!add_attachment_to_buffer({ file_name: elm.files[i].name, file: elm.files[i] }))
      {
        return;
      }
    }
  }
}

Forum.addXEvent(window, 'DOMContentLoaded', function () {
  var elm, idx;
  
  for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    idx = (i == 1) ? '' : i;
    elm = document.getElementById('attachment' + idx);
    if(!elm) continue;
    
    elm.setAttribute('data-idx', idx);
    
    Forum.addXEvent(elm, 'change', function () {
      on_attachment_changed(this.getAttribute('data-idx'));
    });    
  }
});
