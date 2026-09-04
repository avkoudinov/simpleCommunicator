function mustAdjustMultiSelect()
{
  //if(/mobile.*firefox/.test(window.navigator.userAgent.toLowerCase())) return false;

  //if(/edge/.test(window.navigator.userAgent.toLowerCase())) return false;

  if(/(iphone|ipod|ipad|android|iemobile|blackberry|bada|samsungbrowser)/.test(window.navigator.userAgent.toLowerCase())) 
  {
    //alert("must adjust: " + window.navigator.userAgent);
    return true;
  }
  
  if(/Version\/13.+safari/i.test(window.navigator.userAgent.toLowerCase())) 
  {
    //alert("must not adjust: " + window.navigator.userAgent);
    return true;
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
    if(list.options[i].style.display == "none") continue;

    if(!list.options[i].selected) continue;
    
    txt = document.createTextNode(list.options[i].text);
    elm = document.createElement('div');
    if (list.options[i].classList.contains("forum_group_option")) {
      elm.classList.add("forum_group_option_mc");
    }

    elm.setAttribute('data-value', list.options[i].value);
    elm.classList.add('selected');
    elm.appendChild(txt);
    select_control.appendChild(elm);
  }    
  
  for(i = 0; i < list.options.length; i++)
  {
    if(list.options[i].style.display == "none") continue;

    if(list.options[i].selected) continue;
    
    txt = document.createTextNode(list.options[i].text);
    elm = document.createElement('div');
    if (list.options[i].classList.contains("forum_group_option")) {
      elm.classList.add("forum_group_option_mc");
    }
    
    elm.setAttribute('data-value', list.options[i].value);
    elm.appendChild(txt);
    select_control.appendChild(elm);
  }    

  select_control.scrollTo({
      top: 0,
      left: 0
  });
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

let bodyScrollPosition = 0;

function adjustMutliSelects()
{
  if(!mustAdjustMultiSelect()) return;

  var computedStyle;
  var select_control;
  var i;
  
  var option_selector;
  var option_selector_overlay;

  option_selector_overlay = document.createElement('div');
  option_selector_overlay.classList.add('option_selector_overlay');

  option_selector = document.createElement('div');
  option_selector.classList.add('option_selector');
  
  option_selector_overlay.append(option_selector);
  
  var elm;
  
  elm = document.createElement('div');
  elm.classList.add('option_selector_content');
  option_selector.append(elm);
  
  var option_selector_footer = document.createElement('div');
  option_selector_footer.classList.add('option_selector_footer');
  option_selector.append(option_selector_footer);
  
  elm = document.createElement('button');
  elm.append(document.createTextNode(msg_OK));
  option_selector_footer.append(elm);
  Forum.addXEvent(elm, 'click', function (ev) {
      ev.stopPropagation();
      ev.preventDefault();
      
      const inputs = option_selector_overlay.querySelectorAll('input[type="radio"], input[type="checkbox"], .hidden_option, .forum_group_title');
      
      Array.from(option_selector_overlay.active_list.options).forEach((option, index) => {
          option.selected = inputs[index].checked;
      });
      
      Forum.fireEvent(option_selector_overlay.active_list, 'change');
      option_selector_overlay.classList.remove("active");
      //document.body.classList.remove("option_selector_body");
      //document.body.style.top = '';
      //window.scrollTo(0, scrollPosition);
      
      document.querySelectorAll('.field_lookup_area').forEach(function(element) {
          element.style.display = 'none';
      });
  });    

  elm = document.createElement('button');
  elm.append(document.createTextNode(msg_Cancel));
  option_selector_footer.append(elm);
  Forum.addXEvent(elm, 'click', function (ev) {
      option_selector_overlay.classList.remove("active");
      //document.body.classList.remove("option_selector_body");
      //document.body.style.top = '';
      //window.scrollTo(0, scrollPosition);
      
      document.querySelectorAll('.field_lookup_area').forEach(function(element) {
          element.style.display = 'none';
      });
  });    

  document.body.append(option_selector_overlay);

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
    
    Forum.addXEvent(select_control, 'click', function (ev) {
        ev.stopPropagation();
        ev.preventDefault();
        
        showOptionSelector(this.nextSibling, option_selector_overlay);
        
        return false;
    });    

    Forum.addXEvent(selects[i], 'show', function () {
        resizeMutliSelectControl(this, this.previousSibling);
    });    

    Forum.addXEvent(selects[i].form, 'reset', onAuxMultiselectFormReset);    
    
    selects[i].parentNode.insertBefore(select_control, selects[i]);
    
    resizeMutliSelectControl(selects[i], select_control);
    
    select_control.style.display = 'block';
    
    selects[i].style.visibility = "hidden";
    selects[i].style.height = select_control.style.height;
  }
}

function showOptionSelector(list, option_selector_overlay)
{
  const must_hide = list.getAttribute("data-hide-on-show");
  if (must_hide)
  {
      var elm = document.getElementById(must_hide);
      if (elm) elm.style.display = "none";

      elm = document.querySelectorAll("." + must_hide);
      elm.forEach(function(element) {
          element.style.display = 'none';
      });        
  }
  
  //scrollPosition = window.scrollY;
  //document.body.style.top = `-${scrollPosition}px`;
  //document.body.classList.add("option_selector_body");
  option_selector_overlay.classList.add("active");
  
  option_selector_overlay.active_list = list;

  const option_selector_content = option_selector_overlay.querySelector('.option_selector_content');

  while (option_selector_content.firstChild) {
      option_selector_content.removeChild(option_selector_content.firstChild);
  }
  
  const isMultiple = list.hasAttribute('multiple');
  const inputType = isMultiple ? 'checkbox' : 'radio';
  const inputName = 'option-select';

  const table = document.createElement('table');
  
  Array.from(list.options).forEach((option, index) => {
      const tr = document.createElement('tr');
      
      if (option.style.display == "none") {
          const tdDummy = document.createElement('td');
          tdDummy.classList.add("hidden_option");
          tdDummy.setAttribute('colspan', '2');

          tr.appendChild(tdDummy);
          tr.style.display = "none";
      }
      else if (option.classList.contains("forum_group_option")) {
          const tdInput = document.createElement('td');
          tdInput.classList.add("forum_group_title");
          tdInput.appendChild(document.createTextNode(option.text));
          tdInput.setAttribute('colspan', '2');

          tr.appendChild(tdInput);
      } else {
          const tdInput = document.createElement('td');
          const input = document.createElement('input');
          input.type = inputType;
          input.value = option.value;
          input.id = `option-${index}`;
          input.name = inputName;
          input.checked = option.selected; 
          input.dataset.index = index; 
          tdInput.appendChild(input);
          
          const tdLabel = document.createElement('td');
          const label = document.createElement('label');
          label.htmlFor = `option-${index}`;
          label.textContent = option.text;
          tdLabel.appendChild(label);
          
          tr.appendChild(tdInput);
          tr.appendChild(tdLabel);
      }
      
      table.appendChild(tr);
  });
  
  option_selector_content.appendChild(table);
}

Forum.addXEvent(window, 'DOMContentLoaded', function () {
  adjustMutliSelects();
});
