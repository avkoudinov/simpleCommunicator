<script>
var post_comment_action = null;
function show_post_comment(title, author, pid, mode)
{
  hide_all_popups();

  writing_message = pid;

  post_comment_action = function() {
    var elm = document.getElementById('post_comment');
    if(!elm) return;
    do_action({ topic_action: 'comment_message', comment_mode: mode, pid: pid, post_comment: elm.value });
  };

  Forum.on_lightbox_close = function () {
      writing_message = false; 
  };

  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Send")); ?>",
      addClass: "send_button",
      handler: function() {
        post_comment_action();
      }
    }
  ];

  var elm = document.getElementById('comment_author');
  if(elm) elm.innerHTML = Forum.escape_html(author);

  Forum.show_post_comment(title, buttons, 600);

  return false;
}

function move_posts(action)
{
  var new_topic = "";

  var elm = document.getElementById("new_topic");
  if(elm) new_topic = elm.value;

  elm = document.getElementById("found_topics");
  if(!elm) return false;

  if(!elm.value && !new_topic)
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoTopicSelected")); ?>", 'icon-error.gif', mbuttons);

    return;
  }

  do_action({ topic_action: action, forum: "<?php echo_js($fid); ?>", topic: "<?php echo_js($tid); ?>", target_topic: elm.value, new_topic: new_topic, in_search: in_search });
}

function select_target_topic_for_move(action)
{
  if(Forum.isEmptyObject(selected_posts))
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoPostSelected")); ?>", 'icon-error.gif', mbuttons);

    return false;
  }

  hide_all_popups();

  topic_choose_apply_func = move_posts;
  topic_choose_apply_func.action = action;

  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Apply")); ?>",
      addClass: "send_button",
      handler: function() { topic_choose_apply_func(topic_choose_apply_func.action); }
    }
  ];

  Forum.show_topic_selector("<?php echo_js(text("MovePosts")); ?>", buttons, in_search ? false : true, false, 600);

  return false;
}

function update_posts(params)
{
  var elm;
  var dlink;
  
  var action = params.topic_action;

  for(var p in selected_posts)
  {
    elm = document.getElementById("post_head_" + p);
    if(!elm) continue;

    elm.parentNode.classList.remove('selected_post_row');

    if(action == "delete_post")
    {
      dlink = document.getElementById("delete_restore_link_" + p);
      if(!dlink) continue;
      
      elm.classList.add('deleted_post');

      params.topic_action = 'restore_post';
      dlink.innerHTML = "<?php echo_js(text("Restore")); ?>";
      dlink.onclick = function (event) { return select_and_do(this, params); }
    }
    else if(action == "restore_post")
    {
      dlink = document.getElementById("delete_restore_link_" + p);
      if(!dlink) continue;

      elm.classList.remove('deleted_post');

      params.topic_action = 'delete_post';
      dlink.innerHTML = "<?php echo_js(text("Delete")); ?>";
      dlink.onclick = function (event) { return select_and_confirm('<?php echo_js(text("MsgConfirmPostsDelete"), true); ?>', this, params); }
    }
    else if(action == "convert_to_comment")
    {
      elm = document.getElementById("post_table_" + p);
      if(elm) 
      {
        elm.classList.remove('thematic_post');
        elm.classList.add('comment_post');
      } 
      
      dlink = document.getElementById("convert_link_" + p);
      if(!dlink) continue;

      params.topic_action = 'convert_to_thematic';
      dlink.innerHTML = "<?php echo_js(text("MakePostThematic")); ?>";
      dlink.onclick = function (event) { return select_and_do(this, params); }
    }
    else if(action == "convert_to_thematic")
    {
      elm = document.getElementById("post_table_" + p);
      if(elm) 
      {
        elm.classList.remove('comment_post');
        elm.classList.add('thematic_post');
      } 

      dlink = document.getElementById("convert_link_" + p);
      if(!dlink) continue;

      params.topic_action = 'convert_to_comment';
      dlink.innerHTML = "<?php echo_js(text("MakePostToComment")); ?>";
      dlink.onclick = function (event) { return select_and_do(this, params); }
    }
    else if(action == "convert_to_adult")
    {
      elm = document.getElementById("post_table_" + p);
      if(elm) 
      {
        elm.classList.add('adult_post');
      } 
      
      dlink = document.getElementById("convert_adult_" + p);
      if(!dlink) continue;

      params.topic_action = 'convert_to_nonadult';
      dlink.innerHTML = "<?php echo_js(text("MakePostNonAdult")); ?>";
      dlink.onclick = function (event) { return select_and_do(this, params); }

      elm = document.getElementById("adult_tag_" + p);
      if(elm) 
      {
        elm.style.display = "block";
      } 
    }
    else if(action == "convert_to_nonadult")
    {
      elm = document.getElementById("post_table_" + p);
      if(elm) 
      {
        elm.classList.remove('adult_post');
      } 

      dlink = document.getElementById("convert_adult_" + p);
      if(!dlink) continue;

      params.topic_action = 'convert_to_adult';
      dlink.innerHTML = "<?php echo_js(text("MakePostAdult")); ?>";
      dlink.onclick = function (event) { return select_and_do(this, params); }

      elm = document.getElementById("adult_tag_" + p);
      if(elm) 
      {
        elm.style.display = "none";
      } 
    }
    else if(action == "move_posts")
    {
      Forum.hide_sys_lightbox();
      
      elm = document.getElementById("post_" + p);
      if(elm) 
      {
        elm.remove();
      } 
    }

    delete selected_posts[p];
  }
}

function confirm_action(msg, params)
{
  if((params.topic_action == "delete_post" || 
      params.topic_action == "convert_to_thematic" || params.topic_action == "convert_to_comment" ||
      params.topic_action == "convert_to_adult" || params.topic_action == "convert_to_nonadult") && 
     Forum.isEmptyObject(selected_posts))
  {
    var mbuttons = [
      {
        caption: msg_OK,
        handler: function() { Forum.hide_user_msgbox(); }
      }
    ];

    Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoPostSelected")); ?>", 'icon-error.gif', mbuttons);

    return false;
  }

  if(no_confirmation_of_any_actions == 1 || 
    (no_confirmation_of_uncritical_actions == 1 && params.uncritical) ||
    (no_confirmation_of_dislikes == 1 && params.topic_action == 'rate_post' && params.rating == -1)
    ) 
  {
    Forum.hide_user_msgbox();
    do_action(params);
    return false;
  }

  hide_all_popups();

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

  Forum.show_user_msgbox(msg_Confirmation, msg, 'icon-question.gif', mbuttons);

  return false;
}

function invert_user_subscribe_action(params)
{
  var elms = document.getElementsByClassName('subscribe_action_a_' + params.guest_id);
  for(var i = 0; i < elms.length; i++)
  {
    if(params.subscribe_action == 'unsubscribe_from_user')
    {
      elms[i].title = "<?php echo_js(text("SubscribeToUser")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmSubscribeToUser"), true); ?>".replace(/%s/, params.display_user_name), { subscribe_action: "subscribe_to_user", uid: params.uid, user_name: params.user_name, display_user_name: params.display_user_name, guest_id: params.guest_id }); }
    }
    else if(params.subscribe_action == 'subscribe_to_user')
    {
      elms[i].title = "<?php echo_js(text("UnsubscribeFromUser")); ?>";
      elms[i].onclick = function (event) { return do_action({ subscribe_action: "unsubscribe_from_user", uid: params.uid, user_name: params.user_name, display_user_name: params.display_user_name, guest_id: params.guest_id }); }
    }
  }

  elms = document.getElementsByClassName('subscribe_action_img_' + params.guest_id);
  for(i = 0; i < elms.length; i++)
  {
    if(params.subscribe_action == 'unsubscribe_from_user')
    {
      elms[i].src = "<?php echo($view_path); ?>images/subscribe_to_user.png";
    }
    else
    {
      elms[i].src = "<?php echo($view_path); ?>images/unsubscribe_from_user.png";
    }
  }
}

function invert_hide_profile_action(profile_hide_action, author_name, display_author_name, uid)
{
  var elms = document.getElementsByClassName('hide_profile_a_' + uid);
  for(var i = 0; i < elms.length; i++)
  {
    if(profile_hide_action == 'hide_user_profile')
    {
      elms[i].title = "<?php echo_js(text("OpenProfile")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmProfileOpen"), true); ?>".replace(/%s/, display_author_name), { profile_hide_action: "open_user_profile", uid: uid, author_name: author_name, display_author_name: display_author_name }); }
    }
    else if(profile_hide_action == 'open_user_profile')
    {
      elms[i].title = "<?php echo_js(text("HideProfile")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmProfileHide"), true); ?>".replace(/%s/, display_author_name), { profile_hide_action: "hide_user_profile", uid: uid, author_name: author_name, display_author_name: display_author_name }); }
    }
    else if(profile_hide_action == 'hide_guest_profile')
    {
      elms[i].title = "<?php echo_js(text("OpenProfile")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmProfileOpen"), true); ?>".replace(/%s/, display_author_name), { profile_hide_action: "open_guest_profile", guest_id: uid, guest_name: author_name, display_guest_name: display_author_name }); }
    }
    else if(profile_hide_action == 'open_guest_profile')
    {
      elms[i].title = "<?php echo_js(text("HideProfile")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmProfileHide"), true); ?>".replace(/%s/, display_author_name), { profile_hide_action: "hide_guest_profile", guest_id: uid, guest_name: author_name, display_guest_name: display_author_name }); }
    }
  }

  elms = document.getElementsByClassName('hide_profile_img_' + uid);
  for(i = 0; i < elms.length; i++)
  {
    if(profile_hide_action == 'hide_user_profile' || profile_hide_action == 'hide_guest_profile')
    {
      elms[i].src = "<?php echo($view_path); ?>images/show_profile.png";
    }
    else
    {
      elms[i].src = "<?php echo($view_path); ?>images/hide_profile.png";
    }
  }
}

function invert_ignore_action(ignore_action, author_name, display_author_name, uid, elm_id)
{
  var elms = document.getElementsByClassName('ignore_user_a_' + elm_id);
  for(var i = 0; i < elms.length; i++)
  {
    if(ignore_action == 'put_to_ignore_list')
    {
      elms[i].title = "<?php echo_js(text("RemoveFromIgnoreList")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmUserUnignore"), true); ?>".replace(/%s/, display_author_name), { ignore_action: "remove_from_ignore_list", author_name: author_name, display_author_name: display_author_name, uid: uid }); }
    }
    else if(ignore_action == 'remove_from_ignore_list')
    {
      elms[i].title = "<?php echo_js(text("PutToIgnoreList")); ?>";
      elms[i].onclick = function (event) { return confirm_action_with_comment("<?php echo_js(text("MsgConfirmUserIgnore"), true); ?>".replace(/%s/, display_author_name), { ignore_action: "put_to_ignore_list", author_name: author_name, display_author_name: display_author_name, uid: uid }); }
    }
    else if(ignore_action == 'put_guest_to_ignore_list')
    {
      elms[i].title = "<?php echo_js(text("RemoveFromIgnoreList")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmUserUnignore"), true); ?>".replace(/%s/, display_author_name), { ignore_action: "remove_guest_from_ignore_list", guest_name: author_name, display_guest_name: display_author_name, guest_id: elm_id }); }
    }
    else if(ignore_action == 'remove_guest_from_ignore_list')
    {
      elms[i].title = "<?php echo_js(text("PutToIgnoreList")); ?>";
      elms[i].onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmUserIgnore"), true); ?>".replace(/%s/, display_author_name), { ignore_action: "put_guest_to_ignore_list", guest_name: author_name, display_guest_name: display_author_name, guest_id: elm_id }); }
    }
  }

  elms = document.getElementsByClassName('ignore_user_img_' + elm_id);
  for(i = 0; i < elms.length; i++)
  {
    if(ignore_action == 'put_to_ignore_list' || ignore_action == 'put_guest_to_ignore_list')
    {
      elms[i].src = "<?php echo($view_path); ?>images/unignore_user.png";
    }
    else
    {
      elms[i].src = "<?php echo($view_path); ?>images/ignore_user.png";
    }
  }
}

function convert_action_link(target, params)
{
  var elm;

  if(target == "add_to_favourites")
  {
    elm = document.getElementById("favourites_action");
    if(elm)
    {
      params.topic_action = "add_to_favourites";
      elm.innerHTML = "<?php echo_js(text("AddToFavourites")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfrimAddToFavourites"), true); ?>", params); }
    }
  }

  if(target == "remove_from_favourites")
  {
    elm = document.getElementById("favourites_action");
    if(elm)
    {
      params.topic_action = "remove_from_favourites";
      elm.innerHTML = "<?php echo_js(text("RemoveFromFavourites")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }
  }

  if(target == "add_to_ignored")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      params.topic_action = "add_to_ignored";
      elm.innerHTML = "<?php echo_js(text("AddToIgnoredTopics")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmTopicIgnore"), true); ?>", params); }
    }
  }

  if(target == "remove_from_ignored")
  {
    elm = document.getElementById("ignore_action");
    if(elm)
    {
      params.topic_action = "remove_from_ignored";
      elm.innerHTML = "<?php echo_js(text("RemoveFromIgnoredTopics")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }
  }

  if(target == "subscribe")
  {
    elm = document.getElementById("subscribe_action");
    if(elm)
    {
      params.topic_action = "subscribe";
      elm.innerHTML = "<?php echo_js(text("Subscribe")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmSubscribeToTopic"), true); ?>", params); }
    }
  }

  if(target == "unsubscribe")
  {
    elm = document.getElementById("subscribe_action");
    if(elm)
    {
      params.topic_action = "unsubscribe";
      elm.innerHTML = "<?php echo_js(text("Unsubscribe")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }
  }

  if(target == "pin_user_topic")
  {
    elm = document.getElementById("pin_user_action");
    if(elm)
    {
      params.topic_action = "pin_user_topic";
      elm.innerHTML = "<?php echo_js(text("PinTopic")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmPinTopic"), true); ?>", params); }
    }
  }

  if(target == "unpin_user_topic")
  {
    elm = document.getElementById("pin_user_action");
    if(elm)
    {
      params.topic_action = "unpin_user_topic";
      elm.innerHTML = "<?php echo_js(text("UnpinTopic")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }
  }

  if(target == "make_topic_moderator")
  {
    elm = document.getElementById("topic_moderator_link_" + params.post);
    if(elm)
    {
      params.comment = "";
      params.topic_action = "make_topic_moderator";
      elm.innerHTML = "<?php echo_js(text("MakeAuthorTopicModerator")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmMakeTopicModerator"), true); ?>".replace(/%s/, params.display_author_name), params); }
    }
  }

  if(target == "revoke_topic_moderator")
  {
    elm = document.getElementById("topic_moderator_link_" + params.post);
    if(elm)
    {
      params.comment = "";
      params.topic_action = "revoke_topic_moderator";
      elm.innerHTML = "<?php echo_js(text("RemoveAuthorFromTopicModerator")); ?>";
      elm.onclick = function (event) { return confirm_action_with_comment("<?php echo_js(text("MsgConfirmRemoveFromTopicModerator"), true); ?>".replace(/%s/, params.display_author_name), params); }
    }
  }

  if(target == "block_user_in_topic")
  {
    elm = document.getElementById("topic_block_link_" + params.post);
    if(elm)
    {
      params.comment = "";
      params.topic_action = "block_user_in_topic";
      elm.innerHTML = "<?php echo_js(text("BlockUserInTopic")); ?>";
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmBlockUserInTopic"), true); ?>".replace(/%s/, params.display_author_name), params); }
    }
  }

  if(target == "unblock_user_in_topic")
  {
    elm = document.getElementById("topic_block_link_" + params.post);
    if(elm)
    {
      params.comment = "";
      params.topic_action = "unblock_user_in_topic";
      elm.innerHTML = "<?php echo_js(text("UnblockUserInTopic")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }
  }

  if(target == "pin_post")
  {
    elm = document.getElementById("pin_post_link_" + params.post);
    if(elm)
    {
      params.topic_action = "pin_post";
      elm.innerHTML = "<?php echo_js(text("PinMessage")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }

    elm = document.getElementById("post_table_" + params.post);
    if(elm) elm.classList.remove('pinned_post');
  }

  if(target == "unpin_post")
  {
    elm = document.getElementById("pin_post_link_" + params.post);
    if(elm)
    {
      params.topic_action = "unpin_post";
      elm.innerHTML = "<?php echo_js(text("UnpinMessage")); ?>";
      elm.onclick = function (event) { return do_action(params); }
    }

    elm = document.getElementById("post_table_" + params.post);
    if(elm) elm.classList.add('pinned_post');
  }

  if(target == "add_post_to_favourites")
  {
    elm = document.getElementById("favourite_post_link_" + params.post);
    if(elm)
    {
      params.topic_action = "add_post_to_favourites";
      elm.title = "<?php echo_js(text("AddToFavourites")); ?>";
      elm.classList.remove('post_in_favourites');
      elm.classList.add('post_not_in_favourites');
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfrimAddPostToFavourites")); ?>", params); }
    }
  }

  if(target == "remove_post_from_favourites")
  {
    elm = document.getElementById("favourite_post_link_" + params.post);
    if(elm)
    {
      params.topic_action = "remove_post_from_favourites";
      elm.title = "<?php echo_js(text("RemoveFromFavourites")); ?>";
      elm.classList.remove('post_not_in_favourites');
      elm.classList.add('post_in_favourites');
      elm.onclick = function (event) { return do_action(params); }
    }
  }
  
  if(target == "subscribe_to_post")
  {
    elm = document.getElementById("subscribe_post_link_" + params.post);
    if(elm)
    {
      params.topic_action = "subscribe_to_post";
      elm.title = "<?php echo_js(text("Subscribe")); ?>";
      elm.classList.remove('post_subscribed');
      elm.classList.add('post_not_subscribed');
      elm.onclick = function (event) { return confirm_action("<?php echo_js(text("MsgConfirmSubscribeToPost")); ?>", params); }
    }
  }

  if(target == "unsubscribe_from_post")
  {
    elm = document.getElementById("subscribe_post_link_" + params.post);
    if(elm)
    {
      params.topic_action = "unsubscribe_from_post";
      elm.title = "<?php echo_js(text("Unsubscribe")); ?>";
      elm.classList.remove('post_not_subscribed');
      elm.classList.add('post_subscribed');
      elm.onclick = function (event) { return do_action(params); }
    }
  }
  
  if(target == "add_attachment_to_favourites")
  {
    elm = document.getElementById("favourite_attachment_link_" + params.id);
    if(elm)
    {
      params.topic_action = "add_attachment_to_favourites";
      elm.title = "<?php echo_js(text("AddToFavourites")); ?>";
      elm.classList.remove('post_in_favourites');
      elm.classList.add('post_not_in_favourites');
      elm.onclick = function (event) { return do_action(params); }
    }
  }

  if(target == "remove_attachment_from_favourites")
  {
    elm = document.getElementById("favourite_attachment_link_" + params.id);
    if(elm)
    {
      params.topic_action = "remove_attachment_from_favourites";
      elm.title = "<?php echo_js(text("RemoveFromFavourites")); ?>";
      elm.classList.remove('post_not_in_favourites');
      elm.classList.add('post_in_favourites');
      elm.onclick = function (event) { return do_action(params); }
    }
  }
}

function show_attachment_gallery()
{
  var errbuttons = [
    {
      caption: msg_OK,
      handler: function() { Forum.hide_user_msgbox(); }
    }
  ];
  
  var buttons = [
    {
      caption: "<?php echo_js(text("Cancel")); ?>",
      handler: function() { Forum.hide_sys_lightbox(); }
    },
    {
      caption: "<?php echo_js(text("Apply")); ?>",
      addClass: "send_button",
      handler: function() { 
        if(paste_gallery_attachment_placeholders()) 
          Forum.hide_sys_lightbox(); 
        else
          Forum.show_user_msgbox(msg_Error, "<?php echo_js(text("ErrNoAttachmentSelected")); ?>", 'icon-error.gif', errbuttons);
      }
    }
  ];

  Forum.show_attachment_gallery("<?php echo_js(text("AttachmentGallery")); ?>", buttons);
  
  var sys_lightbox_body = document.getElementById("sys_lightbox_body");
  if(!sys_lightbox_body) return false;
  
  var attachment_gallery_area = sys_lightbox_body.lastChild;
  if(attachment_gallery_area)
  {
    Forum.addXEvent(attachment_gallery_area, 'scroll', function (ev) {
      var gap_to_end = this.scrollHeight - this.offsetHeight - this.scrollTop;
      if(gap_to_end < 200)
      {
        load_next_gallery_attachments("<?php echo_js(text("AddToFavourites")); ?>", "<?php echo_js(text("RemoveFromFavourites")); ?>", last_loaded_att_post_id, last_loaded_att_id);
      }
    });
  }
  
  load_next_gallery_attachments("<?php echo_js(text("AddToFavourites")); ?>", "<?php echo_js(text("RemoveFromFavourites")); ?>", 0, 0);

  return false;
}

function add_form_to_container(cid, pid)
{
  hide_all_popups();

  var container = document.getElementById(cid);
  var form = document.getElementById('post_form');

  if(!container || !form) return;

  if (!writing_message) refresh_captcha();
  
  var elm = document.getElementById('author');
  if(elm)
  {
    <?php if(!$fmanager->is_logged_in() || (!empty($forum_data["user_posting_as_guest"]) && !empty($_SESSION["guest_posting_mode"]))): ?>
    elm.readOnly = false;
    elm.classList.remove('read_only_field');
    <?php else: ?>
    elm.readOnly = true;
    elm.classList.add('read_only_field');
    <?php endif; ?>
  }

  elm = document.getElementById('special_case');
  if(elm)
  {
    elm.value = "";
  }
  
  elm = document.getElementById('subject');
  if(elm)
  {
    elm.readOnly = true;
    elm.classList.add('read_only_field');
  }

  elm = document.getElementById('bottom_new_message');
  if(elm)
  {
    elm.style.display = "none";
  }

  elm = document.getElementById('post_message_caption');
  if(elm) elm.innerHTML = "<?php echo_js(text("NewMessage")); ?>";

  container.appendChild(form);

  if (!writing_message)
  {
    for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
    {
      idx = (i == 1) ? '' : i;

      if(form.elements['attachment' + idx]) 
      {
        setFileInputCaption(form.elements['attachment' + idx], "<?php echo_js(text("AddAttachment")); ?>", true);
        Forum.fireEvent(form.elements['attachment' + idx], 'show');
      }
    }
  }

  break_check_new_messages();
  activate_auto_save();

  writing_message = pid;
  ensure_anchor_visible = pid;

  debug_line('Writing started', 'history');
  if(!window.history.state.is_active || window.history.state.is_active == 1)
  {
    debug_line('The current work_stage slot is not in use or the same usage, reusing it', 'history');
    window.history.replaceState({ work_stage: 1, is_active: 1 }, null, get_history_url());

    // we replace the default history undo action with this action
    debug_line('Previous action removed from the stack', 'history');
    history_undo_actions_stack.pop();
  }
  else
  {
    debug_line('The current work_stage slot is already in use, adding new one', 'history');
    window.history.pushState({ work_stage: 1, is_active: 1 }, null, get_history_url());
  }

  debug_line('Cancel action put to the stack', 'history');
  history_undo_actions_stack.push(handle_writing_cancel);
}

function edit_message(params, response)
{
  var cid = 'post_container_' + params.post;

  var elm = document.getElementById("load_last_version");
  if(elm) elm.style.visibility = has_auto_saved_message ? "visible" : "hidden";

  elm = document.getElementById('enter_password_row');
  if(elm) elm.style.display = "none";

  add_form_to_container(cid, params.post);

  elm = document.getElementById('author');
  if(elm)
  {
    elm.value = response.author;
    elm.defaultValue = elm.value;

    if(response.may_edit_author)
    {
      elm.readOnly = false;
      elm.classList.remove('read_only_field');
    }
    else
    {
      elm.readOnly = true;
      elm.classList.add('read_only_field');
    }
  }

  elm = document.getElementById('post_message_caption');
  if(elm) elm.innerHTML = "<?php echo_js(text("Edit")); ?>";

  elm = document.getElementById('tid');
  if(elm)
  {
    elm.value = response.topic_id;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('subject');
  if(elm)
  {
    elm.value = response.topic_name;
    elm.defaultValue = elm.value;
    
    if(params.subject_editable == 1)
    {
      elm.readOnly = false;
      elm.classList.remove('read_only_field');
    } 
  }

  elm = document.getElementById('profiled_topic');
  if(elm)
  {
    elm.value = response.profiled_topic;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('stringent_rules');
  if(elm)
  {
    elm.value = params.stringent_rules;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('is_thematic');
  if(elm)
  {
    elm.checked = response.is_thematic;
    elm.defaultChecked = elm.checked;
  }

  elm = document.getElementById('is_adult');
  if(elm)
  {
    elm.checked = response.is_adult;
    elm.defaultChecked = elm.checked;
  }

  elm = document.getElementById('message');
  if(!elm) return false;

  elm.value = response.message;
  elm.defaultValue = elm.value;

  elm = document.getElementById('edited_post');
  if(elm)
  {
    elm.value = params.post;
    elm.defaultValue = elm.value;
  }

  elm = document.getElementById('edit_mode');
  if(elm) 
  {
    elm.value = 1;
    elm.defaultValue = elm.value;
  }

  var has_attachment = 0;
  var elm = document.getElementById(cid);
  if(elm) has_attachment = parseInt(elm.getAttribute('data-has-attachment'));
  
  if(has_attachment > 0)
  {
    elm = document.getElementById('additional_attachments_area');
    if(elm) elm.style.display = 'block';
    
    var elm = document.getElementById('post_form');

    for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
    {
      idx = (i == 1) ? '' : i;

      if(elm.elements['attachment' + idx] && (has_attachment & Math.pow(2, i-1)))
      {
        elm.elements['attachment' + idx].setAttribute("data-original_attachment_exists", 1);
        lock_attachment_buffer_slot(idx);
        setFileInputCaption(elm.elements['attachment' + idx], "<?php echo_js(text("ReplaceCurrentAttachment")); ?>", true);
        show_attachment_delete_button(idx);
      }

      if(elm.elements['attachment' + idx]) Forum.fireEvent(elm.elements['attachment' + idx], 'show');
    }
  }
  
  elm = document.getElementById('stringent_rules_warning');
  if(elm)
  {
    elm.style.display = params.stringent_rules == 1 ? 'block' : 'none';
  }

  elm = document.getElementById('profiled_topic_row');
  if(elm)
  {
    elm.style.display = params.profiled_topic == 1 ? 'table-row' : 'none';
  }
  check_thematic();

  focus_message_field();

  return false;
}

var cancel_confirmaction_in_progress = false;

function confirm_cancel(form)
{
  cancel_confirmaction_in_progress = true;
  
  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox();
        cancel_confirmed = true;
        cancel_confirmaction_in_progress = false;
        window.history.back();
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox();
        cancel_confirmed = false;
        cancel_confirmaction_in_progress = false;
        focus_message_field();
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgConfirmPostCancel")); ?>", 'icon-question.gif', mbuttons, false);

  return false;
}

function confirm_reset(form)
{
  var elm;

  if(!Forum.formDirty(form))
  {
    form.elements['message'].value = '';
    form.elements['message'].defaultValue = '';

    form.reset();
    reset_attachment_buffer();

    // For new posts, this flag has no effect.
    // For editing post, resetting means also delete the existing attachments.
    set_attachment_delete_flag('');
    for(var i = 2; i <= ATTACHMENTS_PER_POST; i++)
    {
      set_attachment_delete_flag(i);
    }

    focus_message_field();

    return false;
  }

  var mbuttons = [
    {
      caption: msg_Yes,
      handler: function() {
        Forum.hide_user_msgbox(true);

        form.elements['message'].value = '';
        form.elements['message'].defaultValue = '';
        
        form.reset();
        reset_attachment_buffer();
        
        // For new posts, this flag has no effect.
        // For editing post, resetting means also delete the existing attachments.
        set_attachment_delete_flag('');
        for(var i = 2; i <= ATTACHMENTS_PER_POST; i++)
        {
          set_attachment_delete_flag(i);
        }

        focus_message_field();
      }
    },
    {
      caption: msg_No,
      handler: function() {
        Forum.hide_user_msgbox(true);

        focus_message_field();
      }
    }
  ];

  Forum.show_user_msgbox(msg_Confirmation, "<?php echo_js(text("MsgConfirmPostCancel")); ?>", 'icon-question.gif', mbuttons, false);

  return false;
}

var post_message_ajax = null;

function post_message(action)
{
  var form = document.getElementById('post_form');
  if(!form) return false;

  Forum.show_sys_progress_indicator(true);

  if(!post_message_ajax)
  {
    post_message_ajax = new Forum.AJAX();

    post_message_ajax.timeout = TIMEOUT;

    post_message_ajax.onload = function(text, xml)
    {
      try
      {
        var response = JSON.parse(text);

        if(response.ERROR_ELEMENT == "user_password")
        {
          if(form.elements["user_password"]) form.elements["user_password"].value = "";
        }

        if(form.elements["captcha_field"] && response.ERROR_ELEMENT == "captcha_field")
        {
          form.elements["captcha_field"].value = "";
          show_hide_captcha(true);
        }

        Forum.handle_response_messages(response);

        if(response.success)
        {
          debug_line("We have posted", "posting");
          
          break_auto_save();

          // do not hide captcha upon preview
          if(!response.html) show_hide_captcha(false);

          // we update the author only if no edit mode
          if(form.elements['edit_mode'].value != '1' || response.self_edited == '1') last_author = form.elements['author'].value;

          if(response.double_post)
          {
            debug_line("Double post detected", "posting");

            hide_post_form(form);
            debug_line('Previous action removed from the stack', 'history');
            history_undo_actions_stack.pop();
            debug_line('Go-back action put to the stack', 'history');
            history_undo_actions_stack.push(function () {
              debug_line("Doing back", 'history');
              window.history.back();
            });

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

            check_new_messages();
            
            Forum.show_sys_progress_indicator(false);

            if(response.return_post)
            {
              set_current_post(response.return_post);
            }

            return;
          }

          if(response.target_url)
          {
            hide_post_form(form);
            debug_line('Previous action removed from the stack', 'history');
            history_undo_actions_stack.pop();
            debug_line('Go-back action put to the stack', 'history');
            history_undo_actions_stack.push(function () {
              debug_line("Doing back", 'history');
              window.history.back();
            });

            var was_edit_mode = (form.elements['edit_mode'].value == '1');
            var edited_post = form.elements['edited_post'].value;
            var original_post = form.elements['return_post'].value;
            
            var filtered_comment_posting = form.elements['profiled_topic'].value && filtered_comment_mode;

            debug_line("Original post for this posting is: " + original_post, "posting");

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

            if(response.login_performed)
            {
              delay_redirect(response.target_url);
              return;
            }

            if(was_edit_mode)
            {
              reload_post(edited_post);
              return;
            }

            var highlight_message = '';
            if(response.return_post) 
            {
              highlight_message = response.return_post;
            }
            
            if(do_not_check_new)
            {
              debug_line("New should not be checked", "posting");
              
              if(original_post)
              {
                debug_line("We set the original post as current", "posting");
                
                set_current_post(original_post);
              }

              Forum.show_sys_progress_indicator(false);
            }            
            else if(in_search)
            {
              debug_line("We are in the search mode, no loads", "posting");
              
              load_created_post(response.created_post, original_post);
            }            
            else if(filtered_comment_posting)
            {
              debug_line("We are in the filtered comment posting, load just created post", "posting");
              load_created_post(response.created_post, original_post);
            }
            else if(is_last_page)
            {
              debug_line("We are on the last page, load new posts", "posting");
              exec_load_new_posts(highlight_message, response.target_url);
            }
            else if(all_page_mode)
            {
              debug_line("We are in the all mode, load new posts", "posting");
              exec_load_new_posts(highlight_message, response.target_url);
            }
            else
            {
              debug_line("We are not on the last page, load just created post", "posting");
              load_created_post(response.created_post, original_post);
            }

            return;
          }

          if(response.html)
          {
            var buttons = [
              {
                caption: "<?php echo_js(text("Back")); ?>",
                handler: function() { Forum.hide_sys_lightbox(); }
              },
              {
                caption: "<?php echo_js(text("Send")); ?>",
                addClass: "send_button",
                handler: function() {
                  Forum.hide_sys_lightbox();
                  post_message('post_message');
                }
              }
            ];

            Forum.show_post_preview("<?php echo_js(text("Preview")); ?>", response.html, buttons);
          }
        }
      }
      catch(err)
      {
        Forum.handle_ajax_error(this, err.message, this.last_url, {});
      }

      Forum.show_sys_progress_indicator(false);

      // We halted the auto save while submission of the post.
      // Post was not submitted, so activate auto save again
      activate_auto_save();
    };

    post_message_ajax.onerror = function(error, url, info)
    {
      Forum.show_sys_progress_indicator(false);

      Forum.handle_ajax_error(this, error, url, info);

      // We halted the auto save while submission of the post.
      // Post was not submitted, so activate auto save again
      activate_auto_save();
    };
  } // init ajax
  else
  {
  }

  post_message_ajax.abort();
  post_message_ajax.resetParams();

  break_auto_save();
  break_check_new_messages();

  var formData = new FormData(form);

  formData.append(action, "1");
  formData.append('hash', get_protection_hash());
  formData.append('user_logged', user_logged);  
  formData.append('user_marker', user_marker);
  formData.append('fpage', fpage);

  for(var i = 1; i <= ATTACHMENTS_PER_POST; i++)
  {
    index = i;
    if(index == 1) index = '';
    
    if(attachment_buffer["del_attachment" + index] == 1)
    {
      formData.append("del_attachment" + index, 1);
    }
    
    // if pasted image exists, replace the file field
    if(attachment_buffer["attachment" + index])
    {
      if(formData.delete)
      {
        formData.delete("attachment" + index);
      }
      
      //alert("appending to formData:" + attachment_buffer["attachment" + index].file.name + "/" + attachment_buffer["attachment" + index].file.type + "/" + attachment_buffer["attachment" + index].file.size);
      
      formData.append("attachment" + index, attachment_buffer["attachment" + index].file, attachment_buffer["attachment" + index].file_name);
    }
  }

  post_message_ajax.setFormData(formData);

  // if writing a message takes long time,
  // posting of the message over AJAX may loose the session,
  // although the session of the main script still live.
  post_message_ajax.request("ajax/process.php");

  return false;
} // post_message

</script>