var attachment_buffer = {};

function reset_attachment_buffer()
{
  var idx;
  
  for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    idx = (i == 1) ? '' : i;

    if(attachment_buffer["attachment" + idx]) delete attachment_buffer["attachment" + idx];
  }

  attachment_buffer = {};
}

function set_attachment_delete_flag(index)
{
  attachment_buffer["del_attachment" + index] = 1;
}

function unset_attachment_delete_flag(index)
{
  if(attachment_buffer["del_attachment" + index]) delete attachment_buffer["del_attachment" + index];
}

function lock_attachment_buffer_slot(index)
{
  attachment_buffer["attachment_slot_locked" + index] = 1;
}

function release_attachment_buffer_slot(index)
{
  if(attachment_buffer["attachment_slot_locked" + index]) delete attachment_buffer["attachment_slot_locked" + index];
}

async function check_heic_image(file_data, index)
{
  var re = new RegExp("^(.+)\.heic$", "i");
  var matches = re.exec(file_data.file_name);
  if (!matches) return;

  elm = document.getElementById('attachment' + index);
  if(!elm) return;

  Forum.show_sys_progress_indicator(true);

  setFileInputCaption(elm, msg_ConvertingHEICtoJPG, true);
  
  var targetType = "image/jpeg";
  
  var result = await heic2any({
			blob: file_data.file,
			toType: targetType,
			quality: 0.9 
		});  
  
  file_data.file_name = matches[1] + ".jpg";
  file_data.file = new File([new Blob([result], {type: targetType})], file_data.file_name, {type: targetType});;
  
  //await new Promise(resolve => setTimeout(resolve, 3000));
  //throw new Error("Something went wrong");
}

async function add_attachment_to_buffer(file_data)
{
  if(!file_data.file.size)
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, msg_ErrNoImagesInClipboard, 'icon-error.gif', mbuttons);

    return false;
  }
  
  var index;
  var elm;
  
  for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    index = i;
    if(index == 1) index = '';
    
    elm = document.getElementById('attachment' + index);
    if(!elm) return;
    
    if(!attachment_buffer["attachment" + index] && !attachment_buffer["attachment_slot_locked" + index])
    {
      try 
      {
        await check_heic_image(file_data, index);  
        
        attachment_buffer["attachment" + index] = file_data;
        
        unset_attachment_delete_flag(index);
        lock_attachment_buffer_slot(index);

        await on_attachment_pasted(index);

        Forum.show_sys_progress_indicator(false);

        return true;
      } 
      catch (error) 
      {
        Forum.show_sys_progress_indicator(false);
        
        var mbuttons = [
          {
            caption: msg_OK,
            handler: function() { Forum.hide_user_msgbox(); }
          }
        ];

        resetFileField(elm);
        
        Forum.show_user_msgbox(msg_Error, error.message, 'icon-error.gif', mbuttons);

        return false;
      }
    }
  }
  
  // all slots are used.

  var mbuttons = [
    {
      caption: msg_OK,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Warning, msg_MaxAttachmentCount.replace(/%s/, ATTACHMENTS_PER_POST), 'icon-warning.gif', mbuttons);

  return false;
}

async function replace_attachment_in_buffer(file_data, index)
{
  elm = document.getElementById('attachment' + index);
  if(!elm) return false;
  
  try 
  {
    await check_heic_image(file_data, index);  

    attachment_buffer["attachment" + index] = file_data;
    
    unset_attachment_delete_flag(index);
    lock_attachment_buffer_slot(index);

    await on_attachment_pasted(index);

    Forum.show_sys_progress_indicator(false);

    return true;
  } 
  catch (error) 
  {
    Forum.show_sys_progress_indicator(false);
    
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    resetFileField(elm);
    
    Forum.show_user_msgbox(msg_Error, error.message, 'icon-error.gif', mbuttons);

    return false;
  }
}

function delete_attachment_from_buffer(index)
{
  if(attachment_buffer["attachment" + index]) delete attachment_buffer["attachment" + index];
  
  release_attachment_buffer_slot(index);
}

Forum.addXEvent(window, 'DOMContentLoaded', function () {
  var drag_drop_zone = document.getElementById("drag_drop_zone");
  
  drag_drop_zone.addEventListener('dragenter', function (ev) {
      this.classList.add('dragover');
      ev.stopPropagation();
      ev.preventDefault();
  });  

  drag_drop_zone.addEventListener('dragover', function (ev) {
      ev.stopPropagation();
      ev.preventDefault();
  });  

  drag_drop_zone.addEventListener('dragleave', function (ev) {
      this.classList.remove('dragover');
      ev.stopPropagation();
      ev.preventDefault();
  });  

  drag_drop_zone.addEventListener('drop', function (ev) {
      this.classList.remove('dragover');
      ev.stopPropagation();
      ev.preventDefault();
      
      extract_dropped_files(ev.dataTransfer);
      return false;
  });  
  
  drag_drop_zone.addEventListener('paste', async function (ev) {
    
    this.blur();
    
    var img_found = false;
    
    if(ev.clipboardData)
    {
      var items = (ev.clipboardData || ev.originalEvent.clipboardData).items;
      
      for (var i = 0; i < items.length; i++) 
      {
        //alert("pasting (" + i + " of " + items.length + "): " + items[i].kind + "/" + items[i].type + ", " + typeof items[i]);

        var file_name = "pasted_image";
        switch(items[i].type) 
        {
          case "image/gif":
            file_name += ".gif";
            break;
          case "image/png":
            file_name += ".png";
            break;
          case "image/jpg":
          case "image/jpeg":
            file_name += ".jpg";
            break;
            
          default:
            continue;          
        }  
        
        //alert("adding to buffer (" + i + " of " + items.length + "): " + items[i].kind + "/" + items[i].type + ", " + typeof items[i]);
        
        if(!await add_attachment_to_buffer({ file_name: file_name, file: items[i].getAsFile() }))
        {
          return;
        }
        
        img_found = true;
      }
    }
    
    if(!img_found)
    {
      setTimeout(extract_pasted_images, 300);
    }
  });  
});

async function extract_dropped_files(dataTransfer)
{
  for(i = 0; i < dataTransfer.files.length; i++)
  {
    if(!await add_attachment_to_buffer({ file_name: dataTransfer.files[i].name, file: dataTransfer.files[i] }))
    {
      return;
    }
  }
} // extract_dropped_files

async function image_to_file(img)
{
  if (img.src.split(',')[0].indexOf('base64') >= 0)
  {
    var byteString = atob(img.src.split(',')[1]);
    var mimeString = img.src.split(',')[0].split(':')[1].split(';')[0];

    var ia = new Uint8Array(byteString.length);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    
    var blob = new Blob([ia], { type: mimeString });

    var file_name = "pasted_image";
    switch(mimeString) 
    {
      case "image/gif":
        file_name += ".gif";
        break;
      case "image/png":
        file_name += ".png";
        break;
      case "image/jpg":
      case "image/jpeg":
        file_name += ".jpg";
        break;
    }  
    
    extracted_image_counter++;
    
    if(!await add_attachment_to_buffer({ file: blob, file_name: file_name }))
    {
      return false;
    }
  }
  
  return true;
} // image_to_file

function deep_search_images(node)
{
  var children = node.children;
  for (var i = 0; i < children.length; i++) {
    if (children[i] && children[i].tagName !== "IMG") {
      if(!deep_search_images(children[i])) return false;
      
      continue;
    }  
      
    if(!image_to_file(children[i])) return false;
  }

  return true;
}

var extracted_image_counter = 0;


function extract_pasted_images()
{
    var drag_drop_zone = document.getElementById("drag_drop_zone");
    
    extracted_image_counter = 0;
    
    deep_search_images(drag_drop_zone);
    
    drag_drop_zone.innerHTML = "";    

    if(extracted_image_counter == 0)
    {
      var mbuttons = [
        {
          caption: msg_OK,
          handler: function() { Forum.hide_user_msgbox(); }
        }
      ];

      Forum.show_user_msgbox(msg_Error, msg_ErrNoImagesInClipboard, 'icon-error.gif', mbuttons);
    }
    else
    {
      extracted_image_counter = 0;
    }
}
