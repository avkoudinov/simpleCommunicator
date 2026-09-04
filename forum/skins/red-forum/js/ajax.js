/*
Usage example:

function send_post_request()
{
  var ajax = new Forum.AJAX();

  ajax.timeout = 10000; // 10 seconds

  ajax.beforestart = function()
  {
    console.log("ajax beforestart");
  };

  ajax.aftercomplete = function(error)
  {
    if(!error)                    console.log("ajax complete with success"); 
    else if(error == 'UserAbort') console.log("ajax complete with user abort");
    else                          console.log("ajax complete with error: " + error);
  };

  ajax.onload = function(text, xml)
  {
    console.log("ajax success: " + text);
  };

  ajax.onerror = function(error, url, info)
  {
    console.log("ajax error: " + error);
  };

  ajax.setPOST('some_param', "380");
  ajax.setPOST('another_param', "400");

  ajax.request("process.php");
}

function send_get_request()
{
  var ajax = new Forum.AJAX();

  ajax.timeout = 10000; // 10 seconds

  ajax.onload = function(text, xml)
  {
    console.log("ajax success: " + text);
  };

  ajax.onerror = function(error)
  {
    console.log("ajax error: " + error);
  };

  ajax.setGET('some_param', "380");
  ajax.setGET('another_param', "400");

  ajax.request("process.php");
}

function send_form_per_ajax()
{
  var ajax = new Forum.AJAX();

  ajax.timeout = 10000; // 10 seconds

  ajax.onload = function(text, xml)
  {
    console.log("ajax success: " + text);
  };

  ajax.onerror = function(error)
  {
    console.log("ajax error: " + error);
  };

  var form = document.getElementById('my_form');

  var formData = new FormData(form);
  formData.append('some_param', "380");
  formData.append('another_param', "400");

  ajax.setFormData(formData);

  ajax.request(form.action);
}
*/

//----------------------------------------------------------------------
Forum.AJAX = function() {
  // Overridable events

  this.onload  = null;
  this.onerror = null;
  this.beforestart = null;
  this.aftercomplete = null;

  this.error_reported = false;
  this.aftercomplete_reported = false;

  this.running = false;

  this.debug = false;
  
  this.last_url = '';
  this.name = '-';

  this.timeout = 30000;

  this.METHOD = "POST";

  // Arrays for GET and POST parameters

  this.GET  = new Array();
  this.POST = new Array();
  this.HEADERS = new Array();
  this.FORM_DATA = null;

  // Timeout handle, for calling clearTimeout

  this.TIMEOUT_HANDLE = null;

  // Initialize

  this.XHR = new XMLHttpRequest();
}; // constructor
//----------------------------------------------------------------------
Forum.AJAX.prototype.formDataFileFix = function(formData) {
  if(typeof formData.keys == 'undefined') return;

  try {
        if (formData.keys) {
            var formKeysToBeRemoved = [];
            
            var keyIterator = formData.keys();
            
            var item = keyIterator.next();
            
            while (!item.done) {
                var key = item.value;
                
                var fileName = null || formData.get(key)['name'];
                var fileSize = null || formData.get(key)['size'];
                if (fileName != null && fileSize != null && fileName == '' && fileSize == 0) {
                    formKeysToBeRemoved.push(key);
                }
                
                item = keyIterator.next();
            }
            
            for (var i = 0; i < formKeysToBeRemoved.length; i++) {
                if(formData.delete) formData.delete(formKeysToBeRemoved[i]);
            }
        }
    }
    catch(err) {
    }
} // formDataFileFix
//----------------------------------------------------------------------
Forum.AJAX.prototype.resetParams = function()
{
  this.GET  = new Array();
  this.POST = new Array();
  this.HEADERS = new Array();
  this.METHOD = "POST";
  this.FORM_DATA = null;
};
//----------------------------------------------------------------------
Forum.AJAX.prototype.setMethod = function(method)
{
  this.METHOD = method;
};
//----------------------------------------------------------------------
Forum.AJAX.prototype.setHeader = function(hname, value)
{
  this.HEADERS[hname] = value;
};
//----------------------------------------------------------------------
Forum.AJAX.prototype.setGET = function(vname, value)
{
  this.GET[vname] = value;
};
//----------------------------------------------------------------------
Forum.AJAX.prototype.setPOST = function(vname, value)
{
  this.POST[vname] = value;
};
//----------------------------------------------------------------------
Forum.AJAX.prototype.setFormData = function(formData)
{
  this.FORM_DATA = formData;
};
//----------------------------------------------------------------------
Forum.AJAX.prototype.abort = function()
{
  if(this.debug) debug_line(this.name + ': AJAX Request aborted ' + (this.running ? '(was running)' : 'was not running'));
  
  if(!this.running) return;
    
  // Aborting fires the event onreadystatechange
  // as if the server did not respond.
  // To prevent unnecssary error messages, we set
  // error_reported to true.
  
  this.error_reported = true;
  
  if(this.TIMEOUT_HANDLE)
  {
    clearTimeout(this.TIMEOUT_HANDLE);
    this.TIMEOUT_HANDLE = null;
  }
  
  this.running = false;
  this.XHR.abort();
  
  if(!this.aftercomplete_reported && this.aftercomplete !== null) 
  {
    this.aftercomplete_reported = true;
    this.aftercomplete('UserAbort');
  }
};
//----------------------------------------------------------------------
Forum.AJAX.prototype.request = function(file)
{
  if(this.debug) debug_line(this.name + ': AJAX Request started');

  this.aftercomplete_reported = false;
  this.error_reported = false;
  this.running = true;

  var v;
  var post;

  this.XHR.abort(); // Close any other connections
  if(this.TIMEOUT_HANDLE)
  {
    clearTimeout(me.TIMEOUT_HANDLE);
    this.TIMEOUT_HANDLE = null;
  }

  this.last_url = file;

  if(!Forum.isEmptyObject(this.GET))
  {
    if(this.last_url.indexOf("?") == -1) this.last_url += "?";
    else                                 this.last_url += "&";

    for(v in this.GET)
    {
      if(!Object.prototype.hasOwnProperty.call(this.GET, v)) continue;
        
      this.last_url += encodeURIComponent(v) + "=" + encodeURIComponent(this.GET[v]) + "&";
    }
  }

  if(this.beforestart !== null)
  {
    this.beforestart();
  }
  
  if(this.FORM_DATA != null)
  {
    this.formDataFileFix(this.FORM_DATA);
    
    this.XHR.open(this.METHOD, this.last_url, true);

    post = this.FORM_DATA;
  }
  else if(!Forum.isEmptyObject(this.POST))
  {
    this.XHR.open(this.METHOD, this.last_url, true);

    post = "";

    for(v in this.POST)
    {
      if(!Object.prototype.hasOwnProperty.call(this.POST, v)) continue;
      
      post += v + "=" + encodeURIComponent(this.POST[v]) + "&";
    }

    this.XHR.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  }
  else
  {
    this.XHR.open(this.METHOD, this.last_url, true);
    post = null;
  }

  for(v in this.HEADERS)
  {
    if(!Object.prototype.hasOwnProperty.call(this.HEADERS, v)) continue;
    
    this.XHR.setRequestHeader(v, this.HEADERS[v]);
  }

  // For the IE, this should be done after open(),
  // otherwise it is not called.
  // It works in other browsers OK!
  // I spent hours to find out why the IE does not fire this event.

  var me = this;

  this.XHR.onreadystatechange = function()
  {
    if(!me) return;
    
    if(this.readyState == 4)
    {
      if(me.debug) 
      {
        debug_line(me.name + ': AJAX Request ended (readyState:' + this.readyState + ', status:' + this.status + ', statusText:' + this.statusText + ')');
      }
      
      if(me.TIMEOUT_HANDLE)
      {
        clearTimeout(me.TIMEOUT_HANDLE);
        me.TIMEOUT_HANDLE = null;
      }
      
      me.running = false;
      
      if(this.status == 200)
      {
        // sucess
        if(me.onload !== null) me.onload(this.responseText, this.responseXML);
        
        if(me.aftercomplete !== null) me.aftercomplete(null);
      }
      else
      {
        var error = '';
        var info = {};
        
        info.status = this.status;
        
        if(this.statusText == '')
        {
          error = 'NoResponse';
        }
        else
        {
          error = this.statusText;
        }

        if(!me.error_reported && me.onerror !== null)
        {
          me.error_reported = true;
          me.onerror(error, me.last_url, info);
        }        
        
        if(!me.aftercomplete_reported && me.aftercomplete !== null) 
        {
          me.aftercomplete_reported = true;
          me.aftercomplete(error);
        }
      }
    }
    else
    {
      if(me.debug) 
      {
        debug_line(me.name + ': AJAX Request state (readyState:' + this.readyState + ', status:' + this.status + ', statusText:' + this.statusText + ')');
      }
    }
  };

  // native timeout
  
  this.XHR.ontimeout = function()
  {
    if(!me) return;
    
    if(me.debug) debug_line(me.name + ': AJAX Request aborted due to the internal timeout');
    
    if(me.TIMEOUT_HANDLE)
    {
      clearTimeout(me.TIMEOUT_HANDLE);
      me.TIMEOUT_HANDLE = null;
    }
    
    me.running = false;
    
    var info = {};
    info.timeout = me.timeout;

    if(!me.error_reported && me.onerror !== null) 
    {
      me.error_reported = true;
      me.onerror('Timeout', me.last_url, info);
    }
      
    if(!me.aftercomplete_reported && me.aftercomplete !== null) 
    {
      me.aftercomplete_reported = true;
      me.aftercomplete('Timeout');
    }
  };
  
  // debug event
  
  this.XHR.onprogress = function(event) 
  {
    if (event.lengthComputable) 
    {
      if(me.debug) debug_line(me.name + ': AJAX Request progress - ' + Math.round(100 * event.loaded / event.total) + '%');
    }
  };
  
  this.XHR.timeout = parseInt(me.timeout) + 5000;
  this.XHR.send(post);

  // Reset params

  this.resetParams();
  
  // if the native timeout handling fails
  
  this.TIMEOUT_HANDLE = setTimeout(function()
                                   {
                                     if(!me) return;
                                       
                                     if(me.debug) debug_line(me.name + ': AJAX Request aborted due to the timeout handler');

                                     var info = {};
                                     info.timeout = me.timeout;

                                     if(!me.error_reported && me.onerror !== null) 
                                     {
                                       me.error_reported = true;
                                       me.onerror('Timeout', me.last_url, info);
                                     }
                                     
                                     if(!me.aftercomplete_reported && me.aftercomplete !== null) 
                                     {
                                       me.aftercomplete_reported = true;
                                       me.aftercomplete('Timeout');
                                     }
                                     
                                     me.running = false;
                                     me.XHR.abort();
                                   }, me.timeout);
};
//----------------------------------------------------------------------