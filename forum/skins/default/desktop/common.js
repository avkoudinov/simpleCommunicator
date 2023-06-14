Forum.addXEvent(window, 'load', function () { 
  Forum.addXEvent(document.body, 'keydown', function (event) { 
    if(typeof posting_active == 'function' && posting_active()) return;

    event = event || window.event;
    var keyCode = event.keyCode || event.which;
    
    if(event.ctrlKey && keyCode == 37) 
    { 
      if(typeof store_unposted_message == 'function') store_unposted_message();
      if(typeof previous_page_url != 'undefined' && previous_page_url) delay_redirect(previous_page_url); 
    }
    if(event.ctrlKey && keyCode == 39) 
    { 
      if(typeof store_unposted_message == 'function') store_unposted_message();
      if(typeof next_page_url != 'undefined'&& next_page_url) delay_redirect(next_page_url); 
    } 
  });
  
  Forum.addXEvent(window, 'beforeunload', function (e) {
    if(typeof check_new_messages_ajax != 'undefined' && check_new_messages_ajax !== null) check_new_messages_ajax.abort();
  });
});

Forum.addXEvent(window, 'resize', function () { 
  var img = document.getElementById("sys_preview_image");
  if(img) Forum.scale_preview_image(img);
});

if(pin_the_menu)
{
  Forum.addXEvent(window, 'scroll', function (ev) { 
    var float_header_container = document.getElementById("float_header_container");
    if(!float_header_container) return;

    var main_header = document.getElementById("main_header");
    var main_menu = document.getElementById("main_menu");
    if(!main_menu) return;
    
    var rect;

    rect = float_header_container.getBoundingClientRect();
    var float_header_container_height = rect.height;

    var header_height = 0;
    if(main_header)
    {
      rect = main_header.getBoundingClientRect();
      header_height = rect.height;
    }
    
    if(window.scrollY > header_height)
    {
      float_header_container.style.height = float_header_container_height + 'px';
      main_menu.classList.add('header2_fixed');
    }
    else
    {
      float_header_container.style.height = 'auto';
      main_menu.classList.remove('header2_fixed');
    }
  });
}