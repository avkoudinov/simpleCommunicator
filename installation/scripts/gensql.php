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

    case "PostgreSQL":
    {
        if(preg_match("/create table ([^\s]+)/smi", $cmd, $matches))
        {
            $db_obects[$matches[1]] = $matches[1];
        }
        elseif(preg_match("/CREATE\s+(?:OR\s+REPLACE\s+)?PROCEDURE ([^\s\(\)]+)/smi", $cmd, $matches))
        {
            $db_obects[$matches[1] . ":PROCEDURE"] = $matches[1] . ":PROCEDURE";
        }
        elseif(preg_match("/CREATE\s+(?:OR\s+REPLACE\s+)?FUNCTION ([^\s\(\)]+)/smi", $cmd, $matches))
        {
            $db_obects[$matches[1] . ":FUNCTION"] = $matches[1] . ":FUNCTION";
        }
    } // case "PostgreSQL"
    break;

    case "Oracle":
    {
        if(preg_match("/create\s+(?:global\s+temporary\s+)?table\s+([^\s]+)/smi", $cmd, $matches))
        {
            $db_obects[$matches[1]] = $matches[1];
        }
        elseif(preg_match("/CREATE\s+(?:OR\s+REPLACE\s+)?PROCEDURE\s+([^\s\(]+)/smi", $cmd, $matches))
        {
            $db_obects[$matches[1] . ":PROCEDURE"] = $matches[1] . ":PROCEDURE";
        }
        elseif(preg_match("/CREATE\s+(?:OR\s+REPLACE\s+)?FUNCTION\s+([^\s\(]+)/smi", $cmd, $matches))
        {
            $db_obects[$matches[1] . ":FUNCTION"] = $matches[1] . ":FUNCTION";
        }
    } // case "Oracle"
    break;

  } // switch
  
  return true;
} // extract_db_object
//--------------------------------------------------------
/**
 * Parse an Oracle SQL script into individual commands.
 *
 * PowerDesigner exports Oracle DDL using ";" as the statement terminator
 * (standard SQL style). SQL*Plus scripts use "/" on its own line instead.
 * This function handles both styles and mixed files transparently.
 *
 * PL/SQL blocks (BEGIN...END, CREATE PROCEDURE/FUNCTION/TRIGGER ...) contain
 * semicolons inside their bodies. We detect them and collect all lines until
 * the matching END; is found (tracking nested BEGIN/END depth).
 */
function parse_oracle_commands($script)
{
  // Strip block comments /* ... */
  $script = preg_replace('/\/\*.*?\*\//s', '', $script);

  $lines       = explode("\n", $script);
  $cmds        = array();
  $buf         = '';
  $in_plsql    = false;
  $begin_depth = 0;

  foreach ($lines as $line)
  {
    $trimmed = trim($line);

    // Skip blank lines when no statement has started yet
    if ($trimmed === '' && trim($buf) === '') continue;

    // Standalone "/" on its own line — SQL*Plus hard terminator
    if (preg_match('/^\s*\/\s*$/', $line))
    {
      $cmd = trim($buf);
      if ($cmd !== '') $cmds[] = $cmd;
      $buf         = '';
      $in_plsql    = false;
      $begin_depth = 0;
      continue;
    }

    $buf .= $line . "\n";

    if (!$in_plsql)
    {
      // Standalone anonymous block: line starts with BEGIN
      if (preg_match('/^\s*BEGIN\b/i', $trimmed))
      {
        $in_plsql    = true;
        $begin_depth = 1;
      }
      // CREATE ... whose header ends with AS or IS — PL/SQL body follows
      elseif (preg_match('/\b(?:AS|IS)\s*$/i', $trimmed))
      {
        $in_plsql    = true;
        $begin_depth = 0;
      }
    }
    else
    {
      // Track BEGIN / END nesting inside PL/SQL body
      preg_match_all('/\bBEGIN\b/i', $trimmed, $m);
      $begin_depth += count($m[0]);
      preg_match_all('/\bEND\b/i', $trimmed, $m);
      $begin_depth -= count($m[0]);
    }

    // Does this line end with ";" ?
    if (preg_match('/;\s*$/', $trimmed))
    {
      if (!$in_plsql)
      {
        // Normal DDL statement — semicolon ends it; strip trailing semicolon
        $cmd = rtrim(trim($buf), " \t\n\r\0;");
        if ($cmd !== '') $cmds[] = $cmd;
        $buf = '';
      }
      else
      {
        // Inside PL/SQL — only END[...]; at nesting depth <= 0 closes the block
        if ($begin_depth <= 0 && preg_match('/\bEND\b[^;]*;\s*$/i', $trimmed))
        {
          $cmd = rtrim(trim($buf), " \t\n\r\0;");
          if ($cmd !== '') $cmds[] = $cmd;
          $buf         = '';
          $in_plsql    = false;
          $begin_depth = 0;
        }
        // else: semicolon is inside the PL/SQL body — keep accumulating
      }
    }
  } // foreach

  // Flush any remaining content (e.g. last statement without trailing newline)
  $cmd = rtrim(trim($buf), " \t\n\r\0;");
  if ($cmd !== '') $cmds[] = $cmd;

  return $cmds;
} // parse_oracle_commands
//--------------------------------------------------------
function gen_create_objects_sql($db_type)
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

    case "PostgreSQL":
    {
      $sql_cmds = array();
      $proc_cmds = array();

      // strip all comments
      $script = preg_replace("/\/\*.*\*\//", "", $script);

      // find procedures/functions (body between $$ ... $$)
      if(preg_match_all(
          '/(\s*CREATE\s+(?:OR\s+REPLACE\s+)?(?:PROCEDURE|FUNCTION)\s+[\s\S]*?\$\$[\s\S]*?\$\$\s*;)/i',
          $script,
          $matches,
          PREG_PATTERN_ORDER
      ))
      {
          $proc_cmds = $matches[1];
          $script = preg_replace(
              '/\s*CREATE\s+(?:OR\s+REPLACE\s+)?(?:PROCEDURE|FUNCTION)\s+[\s\S]*?\$\$[\s\S]*?\$\$\s*;/i',
              '',
              $script
          );
      }

      // find all other normal sql commands
      $matches = array();
      if(preg_match_all("/(.*);[\r\n]?/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $sql_cmds = $matches[1];
      }

      $cmds = array_merge($cmds, $sql_cmds, $proc_cmds);
    } // case "PostgreSQL"
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

    case "Oracle":
    {
      $cmds = parse_oracle_commands($script);
    } // case "Oracle"
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

  echo "Created: create_tables.sql.php<br/>";
  
  return $cmd_counter;
} // gen_create_objects_sql
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

    case "PostgreSQL":
    {
      $sql_cmds = array();
      $proc_cmds = array();

      // strip all comments
      $script = preg_replace("/\/\*.*\*\//", "", $script);

      // find procedures/functions (body between $$ ... $$)
      if(preg_match_all(
          '/(\s*CREATE\s+(?:OR\s+REPLACE\s+)?(?:PROCEDURE|FUNCTION)\s+[\s\S]*?\$\$[\s\S]*?\$\$\s*;)/i',
          $script,
          $matches,
          PREG_PATTERN_ORDER
      ))
      {
          $proc_cmds = $matches[1];
          $script = preg_replace(
              '/\s*CREATE\s+(?:OR\s+REPLACE\s+)?(?:PROCEDURE|FUNCTION)\s+[\s\S]*?\$\$[\s\S]*?\$\$\s*;/i',
              '',
              $script
          );
      }

      // find all other normal sql commands
      $matches = array();
      if(preg_match_all("/(.*);[\r\n]?/smiU", $script, $matches, PREG_PATTERN_ORDER))
      {
        $sql_cmds = $matches[1];
      }

      $cmds = array_merge($cmds, $sql_cmds, $proc_cmds);
    } // case "PostgreSQL"
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

    case "Oracle":
    {
      $cmds = parse_oracle_commands($script);
    } // case "Oracle"
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

$types = array("MySQL", "MSSQL", "PostgreSQL", "Oracle");
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
    echo "Created: init_database.sql.php.\n";
  }
  else
  {
    echo "File does not exists for this database.\n";
  }

  // 2. create tables
  echo "\nProcessing create_tables.sql\n";
  @ob_flush();
  @flush();
  $cnt += gen_create_objects_sql($db_type);

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
    echo "Created: init_data.sql.php.\n";
  }
  else
  {
    echo "File does not exists for this database.\n";
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
    echo "Created: final_actions.sql.php.\n";
  }
  else
  {
    echo "File does not exists for this database.\n";
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