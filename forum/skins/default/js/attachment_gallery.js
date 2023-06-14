function selecte_deselect_gallery_attachment(thumb, single)
{
  if(!single)
  {
    if(thumb.classList.contains('attachment_thumb_selected')) 
      thumb.classList.remove('attachment_thumb_selected');
    else                             
      thumb.classList.add('attachment_thumb_selected');
    
    return;
  }
  
  var elms = document.getElementsByClassName("attachment_thumb_selected");
  for(var i = 0; i < elms.length; i++)
  {
    elms[i].classList.remove('attachment_thumb_selected');
  }
  
  thumb.classList.add('attachment_thumb_selected');
}

function paste_gallery_attachment_placeholders()
{
  var txt = "";
  
  var elms = document.getElementsByClassName("attachment_thumb_selected");
  for(var i = 0; i < elms.length; i++)
  {
    txt += elms[i].getAttribute("data-placeholder");
  }
  
  if(txt == "") return false;
  
  insert_tag("\n\n" + txt, '', 0);
  
  return true;
}

var last_loaded_att_post_id = 0;
var last_loaded_att_id = 0;
var attachment_loading_in_pogress = false;
var load_next_gallery_attachments_ajax = null;
var last_caption = '';

function load_next_gallery_attachments(add_favourite_text, remove_favourite_text, last_att_post_id, last_att_id)
{
  if(attachment_loading_in_pogress) return;
  
  attachment_loading_in_pogress = true;
  
  var thumb, img, a, elm, placeholder, current_caption;
  
  var sys_lightbox_body = document.getElementById("sys_lightbox_body");
  if(!sys_lightbox_body) return false;
  
  var attachment_gallery_area = sys_lightbox_body.lastChild;
  if(!attachment_gallery_area) return false;
  
  atachment_load_indicator = document.createElement("div");
  atachment_load_indicator.classList.add("attachment_thumb");
  atachment_load_indicator.classList.add("attachment_thumb_loading");

  attachment_gallery_area.appendChild(atachment_load_indicator);
  
  if(!load_next_gallery_attachments_ajax)
  {
    load_next_gallery_attachments_ajax = new Forum.AJAX();

    load_next_gallery_attachments_ajax.timeout = TIMEOUT;

    load_next_gallery_attachments_ajax.beforestart = function() { break_check_new_messages(); };
    load_next_gallery_attachments_ajax.aftercomplete = function(error) { activate_check_new_messages(); };

    load_next_gallery_attachments_ajax.onload = function(text, xml)
    {
      this.attachment_gallery_area.removeChild(this.attachment_gallery_area.lastChild);
      
      try
      {
        var response = JSON.parse(text);

        Forum.handle_response_messages(response);

        if(response.success && response.attachments)
        {
          for(var i = 0; i < response.attachments.length; i++)
          {
            if(response.attachments[i].favourite == 1)
            {
              current_caption = msg_Favourites;
            }
            else
            {
              current_caption = response.attachments[i].month + ' ' + response.attachments[i].year;
            }
            
            if(last_caption != current_caption)
            {
              last_caption = current_caption;
              thumb = document.createElement("div");
            thumb.classList.add("attachment_thumb_separator");
              thumb.style.clear = 'both';
              thumb.appendChild(document.createTextNode(current_caption));
              this.attachment_gallery_area.appendChild(thumb);
            }
            
            thumb = document.createElement("div");
            thumb.classList.add("attachment_thumb");
            
            placeholder = "[attachment";
            if(response.attachments[i].nr > 1) placeholder += response.attachments[i].nr; 
            placeholder += "=" + response.attachments[i].post_id + "]\n\n";
            
            thumb.setAttribute("data-placeholder", placeholder);
            
            elm = document.createElement("div");
            elm.classList.add("attachment_area");
            
            Forum.addXEvent(elm, 'click', function (ev) {
              selecte_deselect_gallery_attachment(this.parentNode, true);
              paste_gallery_attachment_placeholders();
              Forum.hide_sys_lightbox(); 
            });
            
            thumb.appendChild(elm);

            a = document.createElement("a");
            a.classList.add("lightbox_image");
            a.href = "ajax/attachment.php?aid=" + response.attachments[i].post_id + "&nr=" + response.attachments[i].nr;
            a.target = "_blank";
            a.lightbox_image_initialized = true;
            Forum.addXEvent(a, 'click', lightbox_image_clicked);
            elm.appendChild(a);
            
            img = new Image();
            img.src = VIEW_PATH + "images/preview_image.png";
            img.classList.add("preview_image_button");
            a.appendChild(img);

            img = new Image();
            img.src = "ajax/attachment.php?aid=" + response.attachments[i].post_id + "&nr=" + response.attachments[i].nr;
            elm.appendChild(img);

            elm = document.createElement("div");
            elm.classList.add("attachment_checkbox");
            Forum.addXEvent(elm, 'click', function (ev) {
              selecte_deselect_gallery_attachment(this.parentNode, false);
            });
            
            thumb.appendChild(elm);
            
            a = document.createElement("a");
            a.classList.add("forum_reference");
            a.href = "topic.php?fid=" + response.attachments[i].forum_id + "&tid=" + response.attachments[i].topic_id + "&msg=" + response.attachments[i].post_id;
            a.target = "_blank";
            a.innerHTML = response.attachments[i].forum_name;
            thumb.appendChild(a);
            
            elm = document.createElement("div");
            elm.classList.add("favourite_action");
            thumb.appendChild(elm);
            
            a = document.createElement("a");
            a.id = "favourite_attachment_link_" + response.attachments[i].id;
            a.classList.add("post_favourite_action");
            a.setAttribute("data-att-id", response.attachments[i].id);
            
            Forum.addXEvent(a, 'mouseout', function (ev) {
              this.blur();
            });
            
            if(response.attachments[i].favourite == 1)
            {
              a.title = remove_favourite_text;
              a.classList.add("post_in_favourites");
              Forum.addXEvent(a, 'click', function (event) {
                event = event || window.event;

                if(event.preventDefault)
                  event.preventDefault();
                else
                  event.returnValue = false;
                
                return do_action({ topic_action: "remove_attachment_from_favourites", id: this.getAttribute("data-att-id") });
              });
            }
            else
            {
              a.title = add_favourite_text;
              a.classList.add("post_not_in_favourites");
              Forum.addXEvent(a, 'click', function (event) {
                event = event || window.event;

                if(event.preventDefault)
                  event.preventDefault();
                else
                  event.returnValue = false;
                
                return do_action({ topic_action: "add_attachment_to_favourites", id: this.getAttribute("data-att-id") });
              });
            }
            a.href = "#";
            a.innerHTML = "&nbsp;";
            elm.appendChild(a);

            this.attachment_gallery_area.appendChild(thumb);
            
            last_loaded_att_post_id = response.attachments[i].last_post_id;
            last_loaded_att_id = response.attachments[i].id;
          }
        }
        
        attachment_loading_in_pogress = false;
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});

        attachment_loading_in_pogress = false;
      }
    };

    load_next_gallery_attachments_ajax.onerror = function(error, url, info)
    {
      this.attachment_gallery_area.removeChild(this.attachment_gallery_area.lastChild);


      Forum.handle_ajax_error(this, error, url, info);
      attachment_loading_in_pogress = false;
    };
  } // init ajax

  load_next_gallery_attachments_ajax.abort();
  load_next_gallery_attachments_ajax.resetParams();

  load_next_gallery_attachments_ajax.attachment_gallery_area = attachment_gallery_area;

  load_next_gallery_attachments_ajax.setPOST('hash', get_protection_hash());
  load_next_gallery_attachments_ajax.setPOST('user_logged', user_logged);
  load_next_gallery_attachments_ajax.setPOST('last_att_post_id', last_att_post_id);
  load_next_gallery_attachments_ajax.setPOST('last_att_id', last_att_id);

  load_next_gallery_attachments_ajax.request("ajax/load_attachments.php");

  return false;
} // load_next_gallery_attachments

function show_hide_attachment_favourite_load(aid, state)
{
  var elm = document.getElementById("favourite_attachment_link_" + aid);
  if(!elm) return;

  if(state) elm.classList.add("post_favourites_loading");
  else      elm.classList.remove("post_favourites_loading");
}

