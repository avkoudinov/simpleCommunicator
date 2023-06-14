function resetFileField(file_input)
{
  file_input.value = '';

  if(!/safari/i.test(navigator.userAgent)){
    file_input.type = '';
    file_input.type = 'file';
  }
  
  Forum.fireEvent(file_input, 'change');
}

function refreshFileInputControl(file_input)
{
  if(!file_input.previousSibling || !file_input.previousSibling.classList.contains('file_input_control')) return;

  if(file_input.value == '')
  {
    file_input.previousSibling.style.color = 'gray';
    file_input.previousSibling.innerHTML = '&nbsp;' + file_input.getAttribute('placeholder');
  }
  else
  {
    file_input.previousSibling.style.color = 'inherit';
    file_input.previousSibling.innerHTML = '&nbsp;' + file_input.value.replace(/\\/g, '/').replace( /.*\//, '');
  }
}

function setFileInputCaption(file_input, caption, gray)
{
  if(!file_input.previousSibling || !file_input.previousSibling.classList.contains('file_input_control')) return;
  
  if(gray)
  {
    file_input.previousSibling.style.color = 'gray';
  }
  else
  {
    file_input.previousSibling.style.color = 'inherit';
  }

  file_input.previousSibling.innerHTML = '&nbsp;' + caption;
}

function resizeFileInputControl(file_input)
{
  if(!file_input.previousSibling || !file_input.previousSibling.classList.contains('file_input_control')) return;

  var computedStyle;
  var width;
  var height;

  computedStyle = getComputedStyle(file_input);
  width = parseInt(computedStyle.width, 10);
  height = parseInt(computedStyle.height, 10);
  
  file_input.previousSibling.style.width = width + 'px';
  file_input.previousSibling.style.height = height + 'px';
}

function adjustFileInputs()
{
  var computedStyle;
  var file_input_control;
  var i;
  
  var inputs = document.getElementsByTagName('input');
  if(inputs.length == 0) return;
  
  for(i = 0; i < inputs.length; i++)
  {
    if(inputs[i].type != 'file') continue;
    
    file_input_control = document.createElement('div');
    file_input_control.classList.add('file_input_control');

    file_input_control.style.position = 'absolute';
    file_input_control.style.display = 'none';
    
    inputs[i].parentNode.insertBefore(file_input_control, inputs[i]);

    refreshFileInputControl(inputs[i]);
    resizeFileInputControl(inputs[i]);
    
    file_input_control.style.display = 'inline-block';
    inputs[i].style.opacity = 0;
    
    Forum.addXEvent(inputs[i], 'change', function () {
      refreshFileInputControl(this);
    });    
    
    Forum.addXEvent(inputs[i], 'show', function () {
        resizeFileInputControl(this);
    });    

    if(inputs[i].form.getAttribute('data-reset-event-added')) continue;
    
    inputs[i].form.setAttribute('data-reset-event-added', 1);
    
    Forum.addXEvent(inputs[i].form, 'reset', function() {
      
      var form = this;
      
      // Reset event of the form occurs BEFORE the value are reset.
      // There is no AFTER RESET event. What a shit!
      // This is the livehack!
      window.setTimeout(function () {
        var inputs = form.getElementsByTagName('input');
        if(inputs.length == 0) return;
        
        for(var i = 0; i < inputs.length; i++)
        {
          if(inputs[i].type != 'file') continue;
          
          Forum.fireEvent(inputs[i], 'change');
        }  
      });
    });    
  }  
}

Forum.addXEvent(window, 'DOMContentLoaded', function () {
  adjustFileInputs();
});

Forum.addXEvent(window, 'resize', function () {
  var i;
  
  var inputs = document.getElementsByTagName('input');
  if(inputs.length == 0) return;
  
  for(i = 0; i < inputs.length; i++)
  {
    if(inputs[i].type != 'file') continue;
    
    resizeFileInputControl(inputs[i]);
  }  
});
