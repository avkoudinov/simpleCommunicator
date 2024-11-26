<?php

// ----------------------------------------------------------------- 
// BEGIN: SYSTEM AREA 
// ----------------------------------------------------------------- 

// Do not change anything here!

setlocale(LC_ALL,
          'en_US.UTF-8',
          'ru_RU.UTF-8',
          'de_DE.UTF8',
          'uk_UA.UTF8'
         );

$SUPPORTED_DATABASES = array(
  "MySQL" => "MySQL 8.0.x",
  "MSSQL" => "Microsoft SQL Server 2005 or higher"
);

$SUPPORTED_CODES = array(
    "apache" => "Apache",
    "nginx" => "Nginx",
    "ini" => "INI",
    "bash" => "BASH",
    "makefile" => "Makefile",
    "c" => "C",
    "cpp" => "C++",
    "csharp" => "C#",
    "css" => "CSS",
    "delphi" => "Delphi",
    "haskell" => "Haskell",
    "java" => "Java",
    "kotlin" => "Kotlin",
    "swift" => "Swift",
    "html" => "HTML",
    "javascript" => "JavaScript",
    "typescript" => "TypeScript",
    "json" => "JSON",
    "perl" => "PERL",
    "php" => "PHP",
    "powershell" => "PowerShell",
    "python" => "Python",
    "scala" => "Scala",
    "ruby" => "Ruby",
    "rust" => "Rust",
    "sql" => "SQL",
    "pgsql" => "PL/pgSQL",
    "vb" => "VB.Net",
    "vba" => "VBA",
    "vbs" => "VBScript",
    "xml" => "XML",
    "yaml" => "YAML"
);

asort($SUPPORTED_CODES, SORT_LOCALE_STRING);

$ACTIVE_LANGUAGES = array(
"en",
"ru",
"de",
"ua"
);

$LANGUAGE_MAPPINGS = array(
"en" => "en_EN",
"ru" => "ru_RU",
"de" => "de_DE",
"ua" => "uk_UA"
);

// ----------------------------------------------------------------- 
// END: SYSTEM AREA 
// ----------------------------------------------------------------- 

// ----------------------------------------------------------------- 
// BEGIN: USER AREA 
// ----------------------------------------------------------------- 

// You can change anything here!

define('DEFAULT_SKIN', 'default');
define('DEFAULT_LANGUAGE', 'ru');
define('TIME_ZONE', 'Europe/Moscow');

define('TOPICS_PER_PAGE', 40);
define('POSTS_PER_PAGE', 25);
define('ATTACHMENTS_PER_POST', 3);

define('NEW_TRACKING_PERIOD', 10); // days
define('NEW_CHECK_FREQUENCY', 30); // seconds
define('NEW_CACHE_TTL', 180); // seconds

define('ALLOW_EDIT_PERIOD', 10); // minutes
define('ALLOW_MODERATE_PERIOD', 14); // days

define('KEEP_ONLINE_PERIOD', 600); // seconds

define('TIMEOUT', 60000); // milliseconds

// The SALT_KEY is created by the installation and is used for hashes.
// Changing it after the installation will cause that 
// the users will be unable to login.
define('SALT_KEY', '59000');

define('HOME_DIRECTORY', '/');

define('CANONICAL_DOMAIN', '');

// special property to disallow new guests
// new guests may write only after this amount of time
// since the first visit
define('MIN_ALLOWED_READMARKER_AGE', 8); // hours

// dos protection, 0 for none
define('MAX_REQUESTS_PER_MINUTE', 30);
define('MAX_STAT_REQUESTS_PER_MINUTE', 10);
define('MAX_POSTS_PER_MINUTE', 10);
define('WAIT_TIME_AFTER_ATTACK', 10); // minutes

define('JOB_PER_CRON', 0);
define('REFRESH_TOR_IPS', 1);

define('BULK_DELETE_COUNT', 5);

define('MAX_SEARCH_RESULTS', 10000);

// The keys and token for retrival of the video meta data

define('GMAPS_API_KEY', '');
define('YOUTUBE_API_KEY', '');
define('VK_CLIENT_SECRET', '');
define('VK_ACCESS_TOKEN', '');

// The email check key
define('BLOCK_DISPOSABLE_EMAIL_KEY', '');

// Using system conversion tools

define('WEB_OPTIMIZED_JPG_SIZE', 600); // KB

define('CONVERT_AMR_TO_MP3', 0); // requires avconv
define('CONVERT_AAC_TO_MP3', 0); // requires avconv
define('CONVERT_M4A_TO_MP3', 0); // requires avconv

define('SUPPORT_LATEX', 0); // requires texlive and dvipng

define('COMPRESS_PNG', 0); // requires pngquant
define('CONVERT_PNG_TO_JPG', 0); // requires imagemagic/convert

// Trace and Debug

define('DEVELOPER_MODE', false);
define('SHOW_MESSAGE_DETAILS', false);
define('SHOW_PROGRAM_WARNINGS', false);
define('TRACE_ERRORS', true);
define('TRACE_ERROR_VARS', false);
define('TRACE_ERROR_ARGS', false);
define('TRACE_STACK', false);
define('MAIL_TO_TRACE', false);

// SMTP settings

ini_set("SMTP", "localhost");
ini_set("smtp_port", 25);

// Database settings

define('DB_TYPE', 'MySQL');
define('DB_SERVER', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_PREFIX', 'V1');

define('DB_FT_MIN_WORD_LEN', '3');

// ----------------------------------------------------------------- 
// END: USER AREA 
// ----------------------------------------------------------------- 
?>