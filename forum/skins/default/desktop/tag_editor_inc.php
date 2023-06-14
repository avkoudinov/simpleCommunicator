<script type='text/JavaScript'> 
function get_selected_tags()
{
  var tags = {};
  
  var tag_list = document.getElementById("tag_list");
  if(!tag_list) return tags;
  
  for(var i = 0; i < tag_list.length; i++)
  {
    if(tag_list.options[i].selected) tags[tag_list.options[i].value] = tag_list.options[i].text;
  }
  
  return tags;
} // get_selected_tags

function delete_tags()
{
  selected_tags = get_selected_tags();
  
  var mbuttons;
  
  if(Forum.isEmptyObject(selected_tags))
  {
    mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoTagSelected")); ?>", 'icon-error.gif', mbuttons);
    
    return;
  }
  
  mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        do_action({ topic_action: "delete_tags" });
      }
    },
    {
      caption: msg_No,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgConfirmTagsDelete"), true); ?>", 'icon-question.gif', mbuttons);
} // delete_tags

function edit_tag()
{
  selected_tags = get_selected_tags();
  
  if(Forum.isEmptyObject(selected_tags))
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoTagSelected")); ?>", 'icon-error.gif', mbuttons);
    
    return;
  }
  
  // we take first
  for(var v in selected_tags)
  {
    show_tag_editor(v.substring(1), selected_tags[v]);
  
    break;
  }
} // edit_tag

function show_tag_editor(tgid, text)
{
  var mbuttons = [
    {
      caption: msg_OK,
      handler: function() { 
        var sys_user_input = document.getElementById("sys_user_input");
        if(!sys_user_input) return;
        
        if(sys_user_input.value == '')
        {
          Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrTagNameEmpty")); ?>", 'icon-error.gif', 
                                  [  
                                    {
                                      caption: msg_OK,
                                      handler: function() { 
                                        Forum.hide_user_msgbox(); 
                                        show_tag_editor(tgid, '');
                                      }
                                    }
                                  ]);
          return;
        }
        
        do_action({ topic_action: "edit_tag", tgid: tgid, tag_name: sys_user_input.value });
      }
    },
    {
      caption: msg_Cancel,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];
  
  Forum.show_user_inputbox("<?php echo_js(text("EditTag")); ?>", "<?php echo_js(text("TagName")); ?>*", text, 'icon-edit.png', mbuttons);
} // show_tag_editor

function merge_tags()
{
  selected_tags = get_selected_tags();
  
  if(Forum.isEmptyObject(selected_tags))
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoTagSelected")); ?>", 'icon-error.gif', mbuttons);
    
    return;
  }
  
  // we take first
  for(var v in selected_tags)
  {
    show_merge_editor(v.substring(1), selected_tags[v]);
  
    break;
  }
} // merge_tags

function show_merge_editor(tgid, text)
{
  var mbuttons = [
    {
      caption: msg_OK,
      handler: function() { 
        var sys_user_input = document.getElementById("sys_user_input");
        if(!sys_user_input) return;
        
        if(sys_user_input.value == '')
        {
          Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrTagNameEmpty")); ?>", 'icon-error.gif', 
                                  [  
                                    {
                                      caption: msg_OK,
                                      handler: function() { 
                                        Forum.hide_user_msgbox(); 
                                        show_merge_editor(tgid, '');
                                      }
                                    }
                                  ]);
          return;
        }
        
        do_action({ topic_action: "merge_tags", tgid: tgid, tag_name: sys_user_input.value });
      }
    },
    {
      caption: msg_Cancel,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];
  
  Forum.show_user_inputbox("<?php echo_js(text("MergeTags")); ?>", "<?php echo_js(text("TagName")); ?>*", text, 'icon-edit.png', mbuttons);
} // show_merge_editor

function add_new_tag2(text)
{
  var mbuttons = [
    {
      caption: msg_OK,
      handler: function() { 
        var sys_user_input = document.getElementById("sys_user_input");
        if(!sys_user_input) return;
        
        if(sys_user_input.value == '')
        {
          Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrTagNameEmpty")); ?>", 'icon-error.gif', 
                                  [  
                                    {
                                      caption: msg_OK,
                                      handler: function() { 
                                        Forum.hide_user_msgbox(); 
                                        add_new_tag('');
                                      }
                                    }
                                  ]);
          return;
        }
        
        do_action({ topic_action: "add_new_tag2", new_tag: sys_user_input.value });
      }
    },
    {
      caption: msg_Cancel,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];
  
  Forum.show_user_inputbox("<?php echo_js(text("AddNewTag")); ?>", "<?php echo_js(text("TagName")); ?>*", text, 'icon-edit.png', mbuttons);
} // add_new_tag2

function new_tag_added2(new_tag, response)
{
  if(response.tag_error)
  {
    Forum.show_user_msgbox(msg_Error, Forum.escape_html(response.tag_error), 'icon-error.gif', 
                            [  
                              {
                                caption: msg_OK,
                                handler: function() { 
                                  Forum.hide_user_msgbox(); 
                                }
                              }
                            ],
                            false,
                            function() { add_new_tag2(new_tag); });
    return;
  }
  
  Forum.hide_user_msgbox(); 
  
  Forum.update_user_tags(response.user_tags);
  
  var tag_list = document.getElementById("tag_list");
  if(!tag_list) return;

  if(response.added_tag)
  {
    Forum.selectValue(tag_list, '#' + response.added_tag);
  }
} // new_tag_added2

function tag_edited(tgid, tag_name, response)
{
  if(response.tag_error)
  {
    Forum.show_user_msgbox(msg_Error, Forum.escape_html(response.tag_error), 'icon-error.gif', 
                            [  
                              {
                                caption: msg_OK,
                                handler: function() { 
                                  Forum.hide_user_msgbox(); 
                                }
                              }
                            ],
                            false,
                            function() { show_tag_editor(tgid, tag_name); });
    return;
  }
  
  Forum.hide_user_msgbox(); 
  
  Forum.update_user_tags(response.user_tags);
  
  var tag_list = document.getElementById("tag_list");
  if(!tag_list) return;

  Forum.selectValue(tag_list, '#' + tgid);
} // tag_edited

function tags_merged(tgid, tag_name, response)
{
  var tag_list = document.getElementById("tag_list");
  if(!tag_list) return;

  if(response.tag_error)
  {
    Forum.show_user_msgbox(msg_Error, Forum.escape_html(response.tag_error), 'icon-error.gif', 
                            [  
                              {
                                caption: msg_OK,
                                handler: function() { 
                                  Forum.hide_user_msgbox(); 
                                }
                              }
                            ],
                            false,
                            function() { show_merge_editor(tgid, tag_name); });
    return;
  }
  
  Forum.hide_user_msgbox(); 
  
  // We cannot use Forum.update_user_tags(response.user_tags);
  // because we should replace removed selected tag with the new one
  
  user_tags = response.user_tags;

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
  
  if(response.merge_target_tag)
  {
    Forum.selectValue(tag_list, '#' + response.merge_target_tag);
  }

  var post_selected_tags;
  var elms = document.getElementsByClassName("manage_tags_list");
  for(var i = 0; i < elms.length; i++)
  {
    post_selected_tags = elms[i].getAttribute("data-selected-tags");
    post_selected_tags = post_selected_tags.split(",");
    
    // we have to replace the швы ща the old selected tags
    // with the id of the merge target tag    
    if(response.merge_target_tag)
    for(var j = 0; j < post_selected_tags.length; j++)
    {
      if(typeof selected_tags[post_selected_tags[j]] != 'undefined')
      {
        post_selected_tags[j] = '#' + response.merge_target_tag;
      }
    }    
    
    rebuild_selected_tag_list(elms[i].getAttribute('data-pid'), post_selected_tags);
  }

} // tag_edited

function show_tag_manager()
{
  hide_all_popups();
  
  var buttons = [
    {
      caption: "<?php echo_js(text("Add")); ?>",
      handler: function() { add_new_tag2(''); }
    },
    {
      caption: "<?php echo_js(text("Edit")); ?>",
      handler: function() { edit_tag(); }
    },
    {
      caption: "<?php echo_js(text("Merge")); ?>",
      handler: function() { merge_tags(); }
    },
    {
      caption: "<?php echo_js(text("Delete")); ?>",
      handler: function() { delete_tags(); }
    },
    {
      caption: "<?php echo_js(text("Close")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    }
  ];

  Forum.show_tag_editor("<?php echo_js(text("ManageTags"), true); ?>", buttons, 600);
} // show_tag_manager
</script>

<div id="tag_editor" class="tag_editor">
    <div class="select_container">
    <select id="tag_list" multiple class="multiple_choice">
    </select>
    </div>
</div>