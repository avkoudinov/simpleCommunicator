function show_debug_console(state)
{
  var cs = document.getElementById("debug_console_container");
  if(!cs) return;
  
  cs.style.display = state ? "block" : "none";

  if (state)
  {
    var dco = document.getElementById("debug_console_output");
    if(dco) dco.scrollTop = dco.scrollHeight;
  }
  
  if(!state) reset_debug_console();
}

var close_debug_timeout = null;
function delay_close_debug_console(delay)
{
  debug_line("Console will be closed in " + delay + " seconds ...");
  close_debug_timeout = setTimeout(function () { show_debug_console(false); }, delay*1000);
}

function select_debug_console_output()
{
  if(close_debug_timeout) clearTimeout(close_debug_timeout);
  
  var dco = document.getElementById("debug_console_output");
  if(!dco) return;
  
  dco.focus();
  dco.select();
}

function reset_debug_console()
{
  var dco = document.getElementById("debug_console_output");
  if(!dco) return;
  
  dco.value = "";
}

function debug_line(str, context = '')
{
  if(context != "force" && !DEBUG_MODE) return;
  
  if(context != "force" && DEBUG_CONTEXT == 'none') return;

  if(DEBUG_CONTEXT != '' && DEBUG_CONTEXT != 'all' && context != "force" && context && context != DEBUG_CONTEXT) return;

  console.log((context && context != "force" ? context + ": " : "") + str);
  
  var dco = document.getElementById("debug_console_output");
  if(!dco) return;

  dco.value += (context && context != "force" ? context + ": " : "") + str + "\n";
  dco.scrollTop = dco.scrollHeight;
}

function log_line(msg)
{
  console.log(msg);

  var log = sessionStorage.getItem('log');
  if(!log)
  {
    log = msg;
  }
  else
  {
    log += "\n" + msg;
  }
  
  sessionStorage.setItem('log', log);
}

function reset_log()
{
  sessionStorage.setItem('log', '');
}

function show_log()
{
  reset_debug_console();
  debug_line(sessionStorage.getItem('log'), 'force');
  show_debug_console(true);
}