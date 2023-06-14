<?php
// The problem is that the concurrent ajax requests are blocking each other,
// because the session file is locked with exclusive lock.
// That means, that one script has to wait until another script completes
// and release the lock.
// Event if you are going to do
// session_start()
// session_write_close()
// you will wait if another sctipt holds the lock.
//
// The solution is trying to read the session data directly from the session file.
// If it is not possible, we use the smallest evil and do
// session_start()
// session_write_close()

function save_session()
{
    global $_SESSION;
    
    $_CURRENT_SESSION = $_SESSION;

    session_start();
    
    $_SESSION = $_CURRENT_SESSION;
    
    session_write_close();
}

function session_start_readonly()
{
  global $_SESSION;
  
  $session_path = session_save_path();
  if(empty($session_path)) $session_path = sys_get_temp_dir();
  
  $session_path = rtrim($session_path, '/\\');

  if(empty($_COOKIE[session_name()]))
  {
    session_start();
    session_write_close();
    return false;
  } else {
      session_id($_COOKIE[session_name()]);
  }
  
  $session_name = preg_replace('/[^\da-z]/i', '', $_COOKIE[session_name()]);
  
  if(!file_exists($session_path . '/sess_' . $session_name) ||
     !is_readable($session_path . '/sess_' . $session_name))
  {
    session_start();
    session_write_close();
    return false;
  }  

  $session_data = file_get_contents($session_path . '/sess_' . $session_name);
  if(empty($session_data))
  {
    session_start();
    session_write_close();
    return false;
  }
  
  $offset = 0;
  
  while ($offset < strlen($session_data)) 
  {
    if(!strstr(substr($session_data, $offset), "|")) break;

    $pos = strpos($session_data, "|", $offset);
    $num = $pos - $offset;
    $varname = substr($session_data, $offset, $num);
    $offset += $num + 1;
    $data = unserialize(substr($session_data, $offset));
    $_SESSION[$varname] = $data;
    $offset += strlen(serialize($data));
  }
  
  return true;
}

session_start_readonly();

/*
$remote_addr = "";
if(!empty($_SERVER['REMOTE_ADDR'])) $remote_addr = $_SERVER['REMOTE_ADDR'];

if (empty($_SESSION['ip']) || $_SESSION['ip'] != $remote_addr) {
  session_destroy();
  unset($_SESSION);
} 
*/

?>