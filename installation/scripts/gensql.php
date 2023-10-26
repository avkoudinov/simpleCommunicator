<?php
ini_set("output_buffering", "off");
ob_start();

@date_default_timezone_set('Universal');

require_once "../forum/include/application_root_inc.php";

// general system predefined config
require_once APPLICATION_ROOT . "include/config_inc.php";

// class autoload policy
require_once APPLICATION_ROOT . "include/define_autoload_inc.php";

// common utility functions
require_once APPLICATION_ROOT . "include/utility_functions_inc.php";

// error handler
require_once APPLICATION_ROOT . "include/error_handler_inc.php";

//--------------------------------------------------------
function extract_db_object($db_type, $cmd, &$db_obects)
{
  switch($db_type)
  {
    case "MySQL":
    {
      if(preg_match("/create table ([^\\s]+)/smi", $cmd, $matches))
      {
        $db_obects[$matches[1]] = $matches[1];
      }
      elseif(preg_match("/CREATE PROCEDURE ([^\\s\\(\\)]+)/smi", $cmd, $matches))
      {
        $db_obects[$matches[1] . ":PROCEDURE"] = $matches[1] . ":PROCEDURE";
      }
      elseif(preg_match("/CREATE FUNCTION ([^\\s\\(\\)]+)/smi", $cmd, $matches))
      {
        $db_obects[$matches[1] . ":FUNCTION"] = $matches[1] . ":FUNCTION";
      }
    } // case "MySQL"
    break;

    case "MSSQL":
    {
      if(preg_match("/create table ([^\\s]+)/smi", $cmd, $matches))
      {
        $db_obects[$matches[1]] = $matches[1];
      }
      elseif(preg_match("/CREATE PROCEDURE ([^\\s\\(\\)]+)/smi", $cmd, $matches))
      {
        $db_obects[$matches[1] . ":PROCEDURE"] = $matches[1] . ":PROCEDURE";
      }
      elseif(preg_match("/CREATE FUNCTION ([^\\s\\(\\)]+)/smi", $cmd, $matches))
      {
        $db_obects[$matches[1] . ":FUNCTION"] = $matches[1] . ":FUNCTION";
      }
    } // case "MSSQL"
    break;
  } // switch
  
  return true;
} // extract_db_object
//--------------------------------------------------------
function gen_create_tables_sql($db_type)
{
  $in_file = APPLICATION_ROOT . "../database/$db_type/create_tables.sql";

  $cmds = array();

  $handle = fopen($in_file, "r");
  if(!$handle)
  {
    die("File $in_file is invalid!");
  }

  $script = fread($handle, filesize($in_file));
  fclose($handle);

  switch($db_type)
  {
    case "MySQL":
    {
      $sql_cmds = array();
      $proc_cmds = array();

      // strip all comments
      $script = preg_replace("/\/\*.*\*\//", "", $script);

      // find the triggers and procs first
      $matches = array();
      if(preg_match_all("/DELIMITER \/\/(.*)\/\/\s+DELIMITER ;/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $proc_cmds = $matches[1];
        // removes this commands from the script
        $script = preg_replace("/DELIMITER \/\/(.*)\/\/\s+DELIMITER ;/smiU", "", $script);
      }

      // find all other normal sql commands
      $matches = array();
      if(preg_match_all("/(.*);[\r\n]?/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $sql_cmds = $matches[1];
      }

      $cmds = array_merge($cmds, $sql_cmds, $proc_cmds);
    } // case "MySQL"
    break;

    case "MSSQL":
    {
      // strip all comments
      $script = preg_replace("/\/\*.*\*\//", "", $script);

      $matches = array();
      if(preg_match_all("/(.*)go[\r\n]+/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $cmds = array_merge($cmds, $matches[1]);
      }
    } // case "MSSQL"
    break;
  } // switch

  if(count($cmds) == 0)
  {
    die("No SQL commands found in the input files - " . $in_file . "!");
  }

  $out_file = APPLICATION_ROOT . "include/$db_type/sql/create_tables.sql.php";
  $handle = fopen($out_file, "w");
  if(!$handle) die("File $out_file is not writable!");

  if(fwrite($handle, "<?php\n\n") === FALSE) die("File $out_file is not writable!");

  $cmd_counter = 0;

  $db_obects = array();

  foreach($cmds as $cmd)
  {
    $cmd = trim($cmd);
    if(empty($cmd)) continue;

    // for MySQL lower 5.1, the trigger cannot be create by the owner of
    // the database. Only the SUPER user could do it.
    // It is fixed starting from the version 5.1.x.
    
    //if($db_type == "MySQL" and stripos($cmd, "TRIGGER") !== FALSE) continue;

    $cmd_str = "\$sql_cmds[] = '\n" . escape_php($cmd) . "\n';";

    extract_db_object($db_type, $cmd_str, $db_obects);
    
    if(fwrite($handle, $cmd_str . "\n\n") === FALSE) die("File $out_file is not writable!");
    
    $cmd_counter++;
  }

  // complete the main file
  if(fwrite($handle, "?>") === FALSE) die("File $out_file is not writable!");
  fclose($handle);

  echo "Created: create_tables.sql.php\n";
  
  return $cmd_counter;
} // gen_create_tables_sql
//--------------------------------------------------------
function gen_sql($db_type, $in_file, $out_file)
{
  if(empty($in_file))
  {
    die("no in file!");
  }

  $cmds = array();

  $handle = fopen($in_file, "r");
  if(!$handle)
  {
    die("File $in_file is invalid!");
  }

  $script = fread($handle, filesize($in_file));
  fclose($handle);

  switch($db_type)
  {
    case "MySQL":
    {
      $sql_cmds = array();
      $proc_cmds = array();

      // strip all comments
      $script = preg_replace("/\/\*.*\*\//", "", $script);

      // find the triggers and procs first
      $matches = array();
      if(preg_match_all("/DELIMITER \/\/(.*)\/\/\s+DELIMITER ;/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $proc_cmds = $matches[1];
        // removes this commands from the script
        $script = preg_replace("/DELIMITER \/\/(.*)\/\/\s+DELIMITER ;/smiU", "", $script);
      }

      // find all other normal sql commands
      $matches = array();
      if(preg_match_all("/(.*);[\r\n]?/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $sql_cmds = $matches[1];
      }

      $cmds = array_merge($cmds, $sql_cmds, $proc_cmds);
    } // case "MySQL"
    break;

    case "MSSQL":
    {
      // strip all comments
      $script = preg_replace("/\/\*.*\*\//", "", $script);

      $matches = array();
      if(preg_match_all("/(.*)go[\r\n]+/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $cmds = array_merge($cmds, $matches[1]);
      }
    } // case "MSSQL"
    break;
  } // switch

  if(count($cmds) == 0)
  {
    die("No SQL commands found in the input file - " . $in_file . "!");
  }

  $handle = fopen($out_file, "w");
  if(!$handle) die("File $out_file is not writable!");

  if(fwrite($handle, "<?php\n\n") === FALSE) die("File $out_file is not writable!");

  $cmd_counter = 0;

  foreach($cmds as $cmd)
  {
    $cmd = trim($cmd);
    if(empty($cmd)) continue;

    $cmd = "\$sql_cmds[] = '\n" . escape_php($cmd) . "\n';";

    if(fwrite($handle, $cmd . "\n\n") === FALSE)
    {
      die("File $out_file is not writable!");
    }

    $cmd_counter++;
  }

  if(fwrite($handle, "?>") === FALSE) die("File $out_file is not writable!");
  fclose($handle);

  return $cmd_counter;
} // gen_sql
//--------------------------------------------------------

$types = array("MySQL", "MSSQL");
foreach($types as $db_type)
{
  $cnt = 0;

  // 1. init database
  echo "\nGenerating install sql commands for $db_type\n\n";
  @ob_flush();
  @flush();

  echo "Processing create_database.sql\n";
  @ob_flush();
  @flush();
  if(file_exists(APPLICATION_ROOT . "../database/$db_type/create_database.sql"))
  {
    $cnt += gen_sql($db_type,
                    APPLICATION_ROOT . "../database/$db_type/create_database.sql",
                    APPLICATION_ROOT . "include/$db_type/sql/create_database.sql.php"
                   );
    echo "Created: create_database.sql.php\n";
  }

  echo "\nProcessing init_database.sql\n";
  @ob_flush();
  @flush();
  if(file_exists(APPLICATION_ROOT . "../database/$db_type/init_database.sql"))
  {
    $cnt += gen_sql($db_type,
                    APPLICATION_ROOT . "../database/$db_type/init_database.sql",
                    APPLICATION_ROOT . "include/$db_type/sql/init_database.sql.php"
                   );
    echo "Created: init_database.sql.php\n";
  }

  // 2. create tables
  echo "\nProcessing create_tables.sql\n";
  @ob_flush();
  @flush();
  $cnt += gen_create_tables_sql($db_type);

  // 3. init data
  if(file_exists(APPLICATION_ROOT . "../database/$db_type/init_data.sql"))
  {
    echo "\nProcessing init_data.sql\n";
    @ob_flush();
    @flush();
    $cnt += gen_sql($db_type,
                    APPLICATION_ROOT . "../database/$db_type/init_data.sql",
                    APPLICATION_ROOT . "include/$db_type/sql/init_data.sql.php"
                   );
    echo "Created: init_data.sql.php\n";
  }

  // 4. final actions
  echo "\nProcessing final_actions.sql\n";
  @ob_flush();
  @flush();
  if(file_exists(APPLICATION_ROOT . "../database/$db_type/final_actions.sql"))
  {
    $cnt += gen_sql($db_type,
                    APPLICATION_ROOT . "../database/$db_type/final_actions.sql",
                    APPLICATION_ROOT . "include/$db_type/sql/final_actions.sql.php"
                   );
    echo "Created: final_actions.sql.php\n";
  }

  echo "\nGenerating update sql commands for $db_type\n";
  
  $files = array();
  
  $dir = APPLICATION_ROOT . "../database/$db_type/updates/";
  
  if(file_exists($dir)) $files = scandir($dir);
  
  foreach($files as $file)
  {
    if($file == "." || $file == ".." || is_dir($dir . $file)) continue;
    
    echo "Processing updates/$file\n";
    @ob_flush();
    @flush();
    $cnt += gen_sql($db_type,
                    $dir . $file,
                    APPLICATION_ROOT . "include/$db_type/sql/updates/$file.php"
                   );
  }  
  
  echo "\n$cnt $db_type commands totally generated!\n\n";
}

?>