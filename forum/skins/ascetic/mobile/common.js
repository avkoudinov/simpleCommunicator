// override default implementation
Forum.invert_pair_checkbox = function(src_chbk, trg_id)
{
  src_chbk.setAttribute("data-is-checked", src_chbk.checked ? 1 : 0);
  
  var elm = document.getElementById(trg_id);

  if(!elm || src_chbk.getAttribute("data-is-checked") != 1 || elm.getAttribute("data-is-checked") != 1) return;

  elm.checked = false;
  elm.setAttribute("data-is-checked", 0);
  
  Forum.fireEvent(elm, 'change');
}; // invert_pair_checkbox

function show_hide_menu()
{
  var elm = document.getElementById("menu_panel");
  if(!elm) return;
  
  if(elm.style.display == "none") elm.style.display = "block";
  else                            elm.style.display = "none";
}

function hide_actions()
{
  var elm = document.getElementById('actions');
  if(!elm) return;
  
  elm.classList.remove('actions_opened');

  elm = document.getElementById('actions_area');
  if(!elm) return;
  
  elm.classList.remove('actions_area_opened');
}

function toggle_actions()
{
  var elm = document.getElementById('actions');
  if(!elm) return;
  
  if(elm.classList.contains('actions_opened'))
  {
    elm.classList.remove('actions_opened');
  }
  else
  {
    elm.classList.add('actions_opened');
  }
  
  elm = document.getElementById('actions_area');
  if(!elm) return;
  
  if(elm.classList.contains('actions_area_opened'))
  {
    elm.classList.remove('actions_area_opened');
  }
  else
  {
    elm.classList.add('actions_area_opened');
  }
}

Forum.addXEvent(window, 'load', function () { 
  Forum.addXEvent(window, 'beforeunload', function (e) {
    if(typeof check_new_messages_ajax != 'undefined' && check_new_messages_ajax !== null) check_new_messages_ajax.abort();
  });
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
      
      header_height += rect.height;
    }

    if(second_menu)
    {
      rect = second_menu.getBoundingClientRect();

      header_height += rect.height;
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

/*
Constant adjustment to screen is not desirable,
because it does not allow zooming.
Forum.addXEvent(window, 'resize', function () { 
  var img = document.getElementById("sys_preview_image");
  if(img) Forum.scale_preview_image(img);
});
*/
