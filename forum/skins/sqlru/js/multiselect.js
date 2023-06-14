function mustAdjustMultiSelect()
{
  //if(/mobile.*firefox/.test(window.navigator.userAgent.toLowerCase())) return false;

  //if(/edge/.test(window.navigator.userAgent.toLowerCase())) return false;
  
  if(/(iphone|ipod|ipad|android|iemobile|blackberry|bada)/.test(window.navigator.userAgent.toLowerCase())) return true;
  
  if(/Version\/13.+safari/i.test(window.navigator.userAgent.toLowerCase())) 
  {
    //alert(window.navigator.userAgent);
    return false;
  }

  return false;
}

function refreshMutliSelectControl(list, select_control)
{
  if(!mustAdjustMultiSelect()) return;

  var elm, txt;
  var i;
  
  while(elm = select_control.lastChild) select_control.removeChild(elm);
  
  for(i = 0; i < list.options.length; i++)
  {
    if(!list.options[i].selected) continue;
    
    txt = document.createTextNode(list.options[i].text);
    elm = document.createElement('div');
    elm.setAttribute('data-value', list.options[i].value);
    elm.classList.add('selected');
    elm.appendChild(txt);
    select_control.appendChild(elm);
  }    
  
  for(i = 0; i < list.options.length; i++)
  {
    if(list.options[i].selected) continue;
    
    txt = document.createTextNode(list.options[i].text);
    elm = document.createElement('div');
    elm.setAttribute('data-value', list.options[i].value);
    elm.appendChild(txt);
    select_control.appendChild(elm);
  }    
}

function resizeMutliSelectControl(list, select_control)
{
  if(!mustAdjustMultiSelect()) return;

  var computedStyle;
  var border_left, border_right, width;
  var border_top, border_bottom, height;

  computedStyle = getComputedStyle(list);
  width = parseInt(computedStyle.width, 10);
  height = parseInt(computedStyle.height, 10);
  
  select_control.style.width = width + 'px';
  select_control.style.height = height + 'px';
}

function onAuxMultiselectFormReset()
{
  var form = this;
  
  window.setTimeout(function () {
    var i;
    
    var selects = form.getElementsByTagName('select');
    if(selects.length == 0) return;
    
    for(i = 0; i < selects.length; i++)
    {
      if(!selects[i].multiple) continue;
      
      if(!selects[i].previousSibling || !selects[i].previousSibling.classList.contains('multiselect_control')) continue;
      
      refreshMutliSelectControl(selects[i], selects[i].previousSibling);
    }  
  });
}

function adjustMutliSelects()
{
  if(!mustAdjustMultiSelect()) return;

  var computedStyle;
  var select_control;
  var i;
  
  var selects = document.getElementsByTagName('select');
  if(selects.length == 0) return;
  
  for(i = 0; i < selects.length; i++)
  {
    if((!selects[i].multiple && selects[i].size < 2) || selects[i].classList.contains('no_multiselect_convert')) continue;
    
    select_control = document.createElement('div');
    select_control.classList.add('multiselect_control');

    select_control.style.position = 'absolute';
    select_control.style.display = 'none';
    
    refreshMutliSelectControl(selects[i], select_control);
    
    Forum.addXEvent(selects[i], 'change', function () {
      if(!this.previousSibling || !this.previousSibling.classList.contains('multiselect_control')) return;
      
      refreshMutliSelectControl(this, this.previousSibling);
    });    
    
    Forum.addXEvent(selects[i], 'show', function () {
        resizeMutliSelectControl(this, this.previousSibling);
    });    

    Forum.addXEvent(selects[i].form, 'reset', onAuxMultiselectFormReset);    
    
    selects[i].parentNode.insertBefore(select_control, selects[i]);
    
    resizeMutliSelectControl(selects[i], select_control);
    
    select_control.style.display = 'block';
    selects[i].style.opacity = 0;
    selects[i].style.height = select_control.style.height;
  }
}

Forum.addXEvent(window, 'DOMContentLoaded', function () {
  adjustMutliSelects();
});
